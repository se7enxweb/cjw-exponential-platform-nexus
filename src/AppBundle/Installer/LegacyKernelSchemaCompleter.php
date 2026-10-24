<?php

declare(strict_types=1);

namespace AppBundle\Installer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

/**
 * Adds to an installed database what the installed Exponential legacy kernel's own schema has
 * and the database lacks.
 *
 * The demo site's schema dumps describe the legacy kernel of the day they were made. A newer
 * kernel (6.0.15, installed on PHP 8.0) reads tables and columns the dumps do not have, for
 * example expbookmark_folder and ezcontentbrowsebookmark.folder_id on every legacy admin page.
 * The kernel's share/db_schema.dba is the generic schema its own installers use for every
 * database engine. Missing tables are created with their keys and indexes; on existing tables
 * missing columns are added with the indexes over them. Nothing that exists is changed.
 */
class LegacyKernelSchemaCompleter
{
    /** @var \Doctrine\DBAL\Connection */
    private $db;

    /** @var string */
    private $schemaFile;

    public function __construct(Connection $db, string $schemaFile)
    {
        $this->db = $db;
        $this->schemaFile = $schemaFile;
    }

    /**
     * @return string[] the SQL statements that were executed
     */
    public function complete(): array
    {
        $definition = $this->loadDefinition();
        if (empty($definition)) {
            return [];
        }

        $current = $this->db->getSchemaManager()->createSchema();
        $target = clone $current;
        foreach ($definition as $tableName => $tableDefinition) {
            if ($tableName[0] === '_' || !isset($tableDefinition['fields'])) {
                continue;
            }
            if ($target->hasTable($tableName)) {
                $this->completeTable($target->getTable($tableName), $tableDefinition);
            } else {
                $this->createTable($target, $tableName, $tableDefinition);
            }
        }

        $queries = (new Comparator())->compare($current, $target)->toSaveSql($this->db->getDatabasePlatform());
        foreach ($queries as $query) {
            $this->db->exec($query);
        }

        return $queries;
    }

    private function loadDefinition(): array
    {
        if (!is_readable($this->schemaFile)) {
            return [];
        }
        $schema = null;
        include $this->schemaFile;

        return is_array($schema) ? $schema : [];
    }

    private function createTable(Schema $schema, string $tableName, array $tableDefinition): void
    {
        $table = $schema->createTable($tableName);
        foreach ($tableDefinition['fields'] as $fieldName => $field) {
            $this->addColumn($table, $fieldName, $field);
        }
        $indexes = $tableDefinition['indexes'] ?? [];
        foreach ($indexes as $indexName => $index) {
            $this->addIndex($table, $indexName, $index);
        }
    }

    private function completeTable(Table $table, array $tableDefinition): void
    {
        $added = [];
        foreach ($tableDefinition['fields'] as $fieldName => $field) {
            if (!$table->hasColumn($fieldName)) {
                $this->addColumn($table, $fieldName, $field);
                $added[] = $fieldName;
            }
        }
        if (empty($added) || empty($tableDefinition['indexes'])) {
            return;
        }
        foreach ($tableDefinition['indexes'] as $indexName => $index) {
            $fields = $this->indexFields($index);
            if ($index['type'] !== 'primary' && !$table->hasIndex($indexName) && array_intersect($fields, $added)) {
                $this->addIndex($table, $indexName, $index);
            }
        }
    }

    private function addColumn(Table $table, string $name, array $field): void
    {
        $length = $field['length'] ?? null;
        $options = ['notnull' => !empty($field['not_null'])];
        switch ($field['type']) {
            case 'auto_increment':
                $type = 'integer';
                $options['notnull'] = true;
                $options['autoincrement'] = true;
                break;
            case 'int':
                $type = 'integer';
                break;
            case 'bigint':
                $type = 'bigint';
                break;
            case 'varchar':
            case 'char':
                $type = 'string';
                $options['length'] = $length ?: 255;
                $options['fixed'] = $field['type'] === 'char';
                break;
            case 'float':
            case 'double':
                $type = 'float';
                break;
            case 'decimal':
                $type = 'decimal';
                [$precision, $scale] = array_pad(explode(',', (string) $length), 2, 0);
                $options['precision'] = (int) $precision ?: 10;
                $options['scale'] = (int) $scale;
                break;
            default:
                // longtext, mediumtext, text: MySQL gives these columns no default
                $type = 'text';
                $field['default'] = null;
        }
        if (isset($field['default']) && $field['default'] !== false) {
            $options['default'] = $field['default'];
        }
        $table->addColumn($name, $type, $options);
    }

    private function addIndex(Table $table, string $name, array $index): void
    {
        $fields = $this->indexFields($index);
        switch ($index['type']) {
            case 'primary':
                $table->setPrimaryKey($fields);
                break;
            case 'unique':
                $table->addUniqueIndex($fields, $name);
                break;
            default:
                $table->addIndex($fields, $name);
        }
    }

    private function indexFields(array $index): array
    {
        $fields = [];
        foreach ($index['fields'] as $field) {
            $fields[] = is_array($field) ? $field['name'] : $field;
        }

        return $fields;
    }
}
