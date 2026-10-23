<?php

declare(strict_types=1);

namespace AppBundle\Installer;

use Doctrine\DBAL\Connection;
use EzSystems\DoctrineSchema\API\Builder\SchemaBuilder;
use EzSystems\PlatformInstallerBundle\Installer\CoreInstaller;

/**
 * Installs the JAC Example demo site: the `cjw-exponential-media` install type (the one the
 * install guide documents) and its older name `exponential-cjw`.
 *
 *  importSchema():
 *    SQLite: creates all tables (Platform, legacy kernel, cjwnl_*) from
 *       src/AppBundle/Resources/database/sql/sqlite/schema.sql
 *       (converted from the MySQL dump by mysql_to_sqlite_cjw.py). It keeps the composite
 *       primary keys (e.g. id+version on ezcontentobject_attribute) of the MySQL structure,
 *       so every content version imports without UNIQUE constraint violations.
 *    MySQL/MariaDB: runs the schema dump of the demo site, schema/schema.sql of the
 *       se7enxweb/cjw-exponential-media-site-data package (the same file is kept in
 *       src/AppBundle/Resources/database/sql/schema/schema.sql).
 *    Other databases: the platform's SchemaBuilder DDL.
 *
 *  importData():
 *    SQLite: src/AppBundle/Resources/database/sql/sqlite/data.sql.
 *    MySQL/MariaDB: the content dump, cjw-exponential-media/data.sql of the data package
 *       (src/AppBundle/Resources/database/sql/data/content.sql in the project).
 *    Other databases: the platform's clean data (no demo content).
 */
class ExponentialCjwInstaller extends CoreInstaller
{
    private const SQLITE_DIR = '/src/AppBundle/Resources/database/sql/sqlite/';

    private const DATA_PACKAGE_DIR = '/vendor/se7enxweb/cjw-exponential-media-site-data/';

    private const PROJECT_SQL_DIR = '/src/AppBundle/Resources/database/sql/';

    /** @var string */
    private $projectDir;

    public function __construct(Connection $db, SchemaBuilder $schemaBuilder, string $projectDir)
    {
        parent::__construct($db, $schemaBuilder);
        $this->projectDir = $projectDir;
    }

    public function importSchema(): void
    {
        switch ($this->getPlatformName()) {
            case 'sqlite':
                $schemaFile = $this->getSqlFile([self::SQLITE_DIR . 'schema.sql']);
                $this->output->writeln(
                    '<info>Creating all tables (eZ Platform + CJW) from <comment>' . $schemaFile . '</comment></info>'
                );
                $this->runQueriesFromFile($schemaFile);
                break;

            case 'mysql':
                $this->runQueriesFromFile($this->getSqlFile([
                    self::DATA_PACKAGE_DIR . 'schema/schema.sql',
                    self::PROJECT_SQL_DIR . 'schema/schema.sql',
                ]));
                break;

            default:
                parent::importSchema();
        }
    }

    public function importData(): void
    {
        switch ($this->getPlatformName()) {
            case 'sqlite':
                $this->runQueriesFromFile($this->getSqlFile([self::SQLITE_DIR . 'data.sql']));
                break;

            case 'mysql':
                $this->runQueriesFromFile($this->getSqlFile([
                    self::DATA_PACKAGE_DIR . 'cjw-exponential-media/data.sql',
                    self::PROJECT_SQL_DIR . 'data/content.sql',
                ]));
                break;

            default:
                $this->output->writeln(
                    '<comment>The JAC Example demo content is provided for MySQL/MariaDB and SQLite only; installing the clean data.</comment>'
                );
                parent::importData();
        }
    }

    /**
     * Splits on statement ends like the parent. On SQLite the two-character sequences "\n"
     * and "\r" of the converted MySQL dump are turned into real characters after splitting,
     * so XML values (e.g. ezimage data_text) are stored as valid XML; each INSERT is already a
     * complete statement then. MySQL/MariaDB read those escapes themselves, so their dumps run
     * unchanged.
     */
    protected function runQueriesFromFile($file)
    {
        $queries = array_filter(preg_split('(;\s*$)m', file_get_contents($file)));
        $convertEscapes = $this->getPlatformName() === 'sqlite';

        if (!$this->output->isQuiet()) {
            $this->output->writeln(
                sprintf(
                    '<info>Executing %d queries from <comment>%s</comment> on database <comment>%s</comment></info>',
                    count($queries),
                    $file,
                    $this->db->getDatabase()
                )
            );
        }

        foreach ($queries as $query) {
            if (trim($query) === '') {
                continue;
            }
            if ($convertEscapes) {
                $query = str_replace(['\\n', '\\r'], ["\n", "\r"], $query);
            }
            $this->db->exec($query);
        }
    }

    private function getPlatformName(): string
    {
        return $this->db->getDatabasePlatform()->getName();
    }

    /**
     * Returns the first readable file of the given paths (relative to the project directory).
     *
     * @param string[] $candidates
     *
     * @throws \RuntimeException if none is readable
     */
    private function getSqlFile(array $candidates): string
    {
        foreach ($candidates as $candidate) {
            $path = $this->projectDir . $candidate;
            if (is_readable($path)) {
                return realpath($path);
            }
        }

        throw new \RuntimeException(
            sprintf(
                'None of the installer SQL files is readable: %s',
                implode(', ', array_map(function (string $candidate): string {
                    return $this->projectDir . $candidate;
                }, $candidates))
            )
        );
    }
}
