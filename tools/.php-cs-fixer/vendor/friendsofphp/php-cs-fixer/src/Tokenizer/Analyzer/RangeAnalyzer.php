<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class RangeAnalyzer
{
    private function __construct()
    {
        // cannot create instance of util. class
    }

    /**
     * Meaningful compare of tokens within ranges.
     *
     * @param array{start: int, end: int} $range1
     * @param array{start: int, end: int} $range2
     */
    public static function rangeEqualsRange(Tokens $tokens, array $range1, array $range2): bool
    {
        $leftStart = $range1['start'];
        $leftEnd = $range1['end'];

        if ($tokens[$leftStart]->isGivenKind([\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT])) {
            $leftStart = $tokens->getNextMeaningfulToken($leftStart);
        }

        while ($tokens[$leftStart]->equals('(') && $tokens[$leftEnd]->equals(')')) {
            $leftStart = $tokens->getNextMeaningfulToken($leftStart);
            $leftEnd = $tokens->getPrevMeaningfulToken($leftEnd);
        }

        $rightStart = $range2['start'];
        $rightEnd = $range2['end'];

        if ($tokens[$rightStart]->isGivenKind([\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT])) {
            $rightStart = $tokens->getNextMeaningfulToken($rightStart);
        }

        while ($tokens[$rightStart]->equals('(') && $tokens[$rightEnd]->equals(')')) {
            $rightStart = $tokens->getNextMeaningfulToken($rightStart);
            $rightEnd = $tokens->getPrevMeaningfulToken($rightEnd);
        }

        $arrayOpenTypes = ['[', [CT::T_ARRAY_INDEX_BRACE_OPEN]];
        $arrayCloseTypes = [']', [CT::T_ARRAY_INDEX_BRACE_CLOSE]];

        while (true) {
            $leftToken = $tokens[$leftStart];
            $rightToken = $tokens[$rightStart];

            if (
                !$leftToken->equals($rightToken)
                && !($leftToken->equalsAny($arrayOpenTypes) && $rightToken->equalsAny($arrayOpenTypes))
                && !($leftToken->equalsAny($arrayCloseTypes) && $rightToken->equalsAny($arrayCloseTypes))
            ) {
                return false;
            }

            $leftStart = $tokens->getNextMeaningfulToken($leftStart);
            $rightStart = $tokens->getNextMeaningfulToken($rightStart);

            $reachedLeftEnd = null === $leftStart || $leftStart > $leftEnd; // reached end left or moved over
            $reachedRightEnd = null === $rightStart || $rightStart > $rightEnd; // reached end right or moved over

            if (!$reachedLeftEnd && !$reachedRightEnd) {
                continue;
            }

            return $reachedLeftEnd && $reachedRightEnd;
        }
    }
}
