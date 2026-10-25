<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Yaml;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Tag\TaggedValue;

/**
 * Limits shared by the block parser and the inline parser of one document.
 *
 * @internal
 */
final class ParserState
{
    public $maxNestingLevel = Parser::DEFAULT_MAX_NESTING_LEVEL;
    public $currentNestingLevel = 0;
    public $maxAliasesForCollections = Parser::DEFAULT_MAX_ALIASES_FOR_COLLECTIONS;
    public $collectionAliasCount = 0;

    public function reset()
    {
        $this->currentNestingLevel = 0;
        $this->collectionAliasCount = 0;
    }

    /**
     * @param int         $line
     * @param string|null $snippet
     * @param string|null $filename
     *
     * @throws ParseException When the document is nested deeper than allowed
     */
    public function enterNestingLevel($line, $snippet, $filename)
    {
        if (++$this->currentNestingLevel > $this->maxNestingLevel) {
            --$this->currentNestingLevel;

            throw new ParseException(sprintf('Maximum nesting depth of %d exceeded.', $this->maxNestingLevel), $line, $snippet, $filename);
        }
    }

    public function leaveNestingLevel()
    {
        if ($this->currentNestingLevel > 0) {
            --$this->currentNestingLevel;
        }
    }

    /**
     * Counts the resolution of an alias that points to a collection.
     *
     * Each such alias copies the whole collection, so a few nested aliases expand exponentially.
     *
     * @param mixed       $refValue
     * @param int         $line
     * @param string|null $snippet
     * @param string|null $filename
     *
     * @throws ParseException When the document resolves more collection aliases than allowed
     */
    public function countAlias($refValue, $line, $snippet, $filename)
    {
        if ($refValue instanceof TaggedValue) {
            $refValue = $refValue->getValue();
        }

        if (!\is_array($refValue) && !$refValue instanceof \stdClass) {
            return;
        }

        if (++$this->collectionAliasCount > $this->maxAliasesForCollections) {
            throw new ParseException(sprintf('Maximum number of collection aliases (%d) exceeded. This limit can be increased with Parser::setMaxAliasesForCollections().', $this->maxAliasesForCollections), $line, $snippet, $filename);
        }
    }
}
