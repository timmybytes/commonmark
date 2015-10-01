<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (http://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Inline\Parser;

use League\CommonMark\Delimiter\Delimiter;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Util\Configuration;
use League\CommonMark\Util\RegexHelper;

class EmphasisParser extends AbstractInlineParser
{
    protected $config;

    public function __construct(array $newConfig = [])
    {
        $this->config = new Configuration([
            'use_asterisk'    => true,
            'use_underscore'  => true,
            'enable_emphasis' => true,
            'enable_strong'   => true,
        ]);
        $this->config->mergeConfig($newConfig);
    }

    /**
     * @return string[]
     */
    public function getCharacters()
    {
        $chars = [];
        if ($this->config->getConfig('use_asterisk', true)) {
            $chars[] = '*';
        }
        if ($this->config->getConfig('use_underscore', true)) {
            $chars[] = '_';
        }

        return $chars;
    }

    /**
     * @param InlineParserContext $inlineContext
     *
     * @return bool
     */
    public function parse(InlineParserContext $inlineContext)
    {
        $character = $inlineContext->getCursor()->getCharacter();
        if (!in_array($character, $this->getCharacters())) {
            return false;
        }

        $numDelims = 0;

        $cursor = $inlineContext->getCursor();
        $charBefore = $cursor->peek(-1);
        if ($charBefore === null) {
            $charBefore = "\n";
        }

        while ($cursor->peek($numDelims) === $character) {
            ++$numDelims;
        }

        // Skip single delims if emphasis is disabled
        if ($numDelims === 1 && !$this->config->getConfig('enable_emphasis')) {
            return false;
        }

        $cursor->advanceBy($numDelims);

        $charAfter = $cursor->getCharacter();
        if ($charAfter === null) {
            $charAfter = "\n";
        }

        $afterIsWhitespace = preg_match('/\pZ|\s/u', $charAfter);
        $afterIsPunctuation = preg_match(RegexHelper::REGEX_PUNCTUATION, $charAfter);
        $beforeIsWhitespace = preg_match('/\pZ|\s/u', $charBefore);
        $beforeIsPunctuation = preg_match(RegexHelper::REGEX_PUNCTUATION, $charBefore);

        $leftFlanking = $numDelims > 0 && !$afterIsWhitespace &&
            !($afterIsPunctuation &&
            !$beforeIsWhitespace &&
            !$beforeIsPunctuation);

        $rightFlanking = $numDelims > 0 && !$beforeIsWhitespace &&
            !($beforeIsPunctuation &&
            !$afterIsWhitespace &&
            !$afterIsPunctuation);

        if ($character === '_') {
            $canOpen = $leftFlanking && (!$rightFlanking || $beforeIsPunctuation);
            $canClose = $rightFlanking && (!$leftFlanking || $afterIsPunctuation);
        } else {
            $canOpen = $leftFlanking;
            $canClose = $rightFlanking;
        }

        $node = new Text($cursor->getPreviousText(), [
            'delim'           => true,
            'emphasis_config' => $this->config,
        ]);
        $inlineContext->getContainer()->appendChild($node);

        // Add entry to stack to this opener
        $delimiter = new Delimiter($character, $numDelims, $node, $canOpen, $canClose);
        $inlineContext->getDelimiterStack()->push($delimiter);

        return true;
    }
}
