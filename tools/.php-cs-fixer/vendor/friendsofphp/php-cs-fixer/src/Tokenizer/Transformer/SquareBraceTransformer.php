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

namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Transform discriminate overloaded square braces tokens.
 *
 * Performed transformations:
 * - in `[1, 2, 3]` into CT::T_ARRAY_BRACKET_OPEN and CT::T_ARRAY_BRACKET_CLOSE,
 * - in `[$a, &$b, [$c]] = array(1, 2, array(3))` into CT::T_DESTRUCTURING_BRACKET_OPEN and CT::T_DESTRUCTURING_BRACKET_CLOSE.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SquareBraceTransformer extends AbstractTransformer
{
    public function getPriority(): int
    {
        // must run after CurlyBraceTransformer and AttributeTransformer
        return -1;
    }

    public function getRequiredPhpVersionId(): int
    {
        // Short array syntax was introduced in PHP 5.4, but the fixer is smart
        // enough to handle it even before 5.4.
        // Same for array destructing syntax sugar `[` introduced in PHP 7.1.
        return 5_00_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('[');
    }

    public function processToken(Tokens $tokens, Token $token, int $index): void
    {
        if ($this->isArrayDestructing($tokens, $index)) {
            $this->transformIntoDestructuringSquareBrace($tokens, $index);

            return;
        }

        if ($this->isShortArray($tokens, $index)) {
            $this->transformIntoArraySquareBrace($tokens, $index);
        }
    }

    public function getCustomTokens(): array
    {
        return [
            CT::T_ARRAY_BRACKET_OPEN,
            CT::T_ARRAY_BRACKET_CLOSE,
            CT::T_DESTRUCTURING_BRACKET_OPEN,
            CT::T_DESTRUCTURING_BRACKET_CLOSE,
        ];
    }

    private function transformIntoArraySquareBrace(Tokens $tokens, int $index): void
    {
        $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_INDEX_BRACKET, $index);

        $tokens[$index] = new Token([CT::T_ARRAY_BRACKET_OPEN, '[']);
        $tokens[$endIndex] = new Token([CT::T_ARRAY_BRACKET_CLOSE, ']']);
    }

    private function transformIntoDestructuringSquareBrace(Tokens $tokens, int $index): void
    {
        $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_INDEX_BRACKET, $index);

        $tokens[$index] = new Token([CT::T_DESTRUCTURING_BRACKET_OPEN, '[']);
        $tokens[$endIndex] = new Token([CT::T_DESTRUCTURING_BRACKET_CLOSE, ']']);

        $previousMeaningfulIndex = $index;
        $index = $tokens->getNextMeaningfulToken($index);

        while ($index < $endIndex) {
            if ($tokens[$index]->equals('[') && $tokens[$previousMeaningfulIndex]->equalsAny([[CT::T_DESTRUCTURING_BRACKET_OPEN], ','])) {
                $tokens[$tokens->findBlockEnd(Tokens::BLOCK_TYPE_INDEX_BRACKET, $index)] = new Token([CT::T_DESTRUCTURING_BRACKET_CLOSE, ']']);
                $tokens[$index] = new Token([CT::T_DESTRUCTURING_BRACKET_OPEN, '[']);
            }

            $previousMeaningfulIndex = $index;
            $index = $tokens->getNextMeaningfulToken($index);
        }
    }

    /**
     * Check if token under given index is short array opening.
     */
    private function isShortArray(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->equals('[')) {
            return false;
        }

        $prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];
        if ($prevToken->equalsAny([
            ')',
            ']',
            '}',
            '"',
            [\T_CONSTANT_ENCAPSED_STRING],
            [\T_STRING],
            [\T_STRING_VARNAME],
            [\T_VARIABLE],
            [CT::T_ARRAY_BRACKET_CLOSE],
            [CT::T_DYNAMIC_PROP_BRACE_CLOSE],
            [CT::T_DYNAMIC_VAR_BRACE_CLOSE],
            [CT::T_ARRAY_INDEX_BRACE_CLOSE],
        ])) {
            return false;
        }

        $nextToken = $tokens[$tokens->getNextMeaningfulToken($index)];
        if ($nextToken->equals(']')) {
            return true;
        }

        return !$this->isArrayDestructing($tokens, $index);
    }

    private function isArrayDestructing(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->equals('[')) {
            return false;
        }

        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        $prevToken = $tokens[$prevIndex];
        if ($prevToken->equalsAny([
            ')',
            ']',
            '"',
            [\T_CONSTANT_ENCAPSED_STRING],
            [\T_STRING],
            [\T_STRING_VARNAME],
            [\T_VARIABLE],
            [CT::T_ARRAY_BRACKET_CLOSE],
            [CT::T_DYNAMIC_PROP_BRACE_CLOSE],
            [CT::T_DYNAMIC_VAR_BRACE_CLOSE],
            [CT::T_ARRAY_INDEX_BRACE_CLOSE],
        ])) {
            return false;
        }

        if ($prevToken->isGivenKind(\T_AS)) {
            return true;
        }

        if ($prevToken->isGivenKind(\T_DOUBLE_ARROW)) {
            $variableIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            if (!$tokens[$variableIndex]->isGivenKind(\T_VARIABLE)) {
                return false;
            }

            $prevVariableIndex = $tokens->getPrevMeaningfulToken($variableIndex);
            if ($tokens[$prevVariableIndex]->isGivenKind(\T_AS)) {
                return true;
            }
        }

        $type = Tokens::detectBlockType($tokens[$index]);
        $end = $tokens->findBlockEnd($type['type'], $index);

        $nextToken = $tokens[$tokens->getNextMeaningfulToken($end)];

        return $nextToken->equals('=');
    }
}
