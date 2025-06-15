<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Analyzer;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\Analyzer\Analysis\ArrayElementAnalysis;

/**
 * @internal
 */
final class ArrayAnalyzer
{
    /**
     * @return list<ArrayElementAnalysis>
     */
    public function getElements(Tokens $tokens, int $index): array
    {
        if ($tokens[$index]->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_OPEN)) {
            $arrayContentStartIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($arrayContentStartIndex));

            $arrayContentEndIndex = $tokens->getPrevMeaningfulToken($tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $index));
            \assert(\is_int($arrayContentEndIndex));

            return $this->getElementsForArrayContent($tokens, $arrayContentStartIndex, $arrayContentEndIndex);
        }

        if ($tokens[$index]->isGivenKind(\T_ARRAY)) {
            $arrayOpenBraceIndex = $tokens->getNextTokenOfKind($index, ['(']);
            \assert(\is_int($arrayOpenBraceIndex));

            $arrayContentStartIndex = $tokens->getNextMeaningfulToken($arrayOpenBraceIndex);
            \assert(\is_int($arrayContentStartIndex));

            $arrayContentEndIndex = $tokens->getPrevMeaningfulToken($tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $arrayOpenBraceIndex));
            \assert(\is_int($arrayContentEndIndex));

            return $this->getElementsForArrayContent($tokens, $arrayContentStartIndex, $arrayContentEndIndex);
        }

        throw new \InvalidArgumentException(\sprintf('Index %d is not an array.', $index));
    }

    /**
     * @return list<ArrayElementAnalysis>
     */
    private function getElementsForArrayContent(Tokens $tokens, int $startIndex, int $endIndex): array
    {
        $elements = [];

        $index = $startIndex;
        while ($endIndex >= $index = $this->nextCandidateIndex($tokens, $index)) {
            if (!$tokens[$index]->equals(',')) {
                continue;
            }

            $elementEndIndex = $tokens->getPrevMeaningfulToken($index);
            \assert(\is_int($elementEndIndex));

            $elements[] = $this->createArrayElementAnalysis($tokens, $startIndex, $elementEndIndex);

            $startIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($startIndex));
        }

        if ($startIndex <= $endIndex) {
            $elements[] = $this->createArrayElementAnalysis($tokens, $startIndex, $endIndex);
        }

        return $elements;
    }

    private function createArrayElementAnalysis(Tokens $tokens, int $startIndex, int $endIndex): ArrayElementAnalysis
    {
        $index = $startIndex;
        while ($endIndex > $index = $this->nextCandidateIndex($tokens, $index)) {
            if (!$tokens[$index]->isGivenKind(\T_DOUBLE_ARROW)) {
                continue;
            }

            $keyEndIndex = $tokens->getPrevMeaningfulToken($index);
            \assert(\is_int($keyEndIndex));

            $valueStartIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($valueStartIndex));

            return new ArrayElementAnalysis($startIndex, $keyEndIndex, $valueStartIndex, $endIndex);
        }

        return new ArrayElementAnalysis(null, null, $startIndex, $endIndex);
    }

    private function nextCandidateIndex(Tokens $tokens, int $index): int
    {
        if ($tokens[$index]->equals('{')) {
            return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $index) + 1;
        }

        if ($tokens[$index]->equals('(')) {
            return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index) + 1;
        }

        if ($tokens[$index]->isGivenKind(CT::T_ARRAY_SQUARE_BRACE_OPEN)) {
            return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $index) + 1;
        }

        if ($tokens[$index]->isGivenKind(\T_ARRAY)) {
            $arrayOpenBraceIndex = $tokens->getNextTokenOfKind($index, ['(']);
            \assert(\is_int($arrayOpenBraceIndex));

            return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $arrayOpenBraceIndex) + 1;
        }

        return $index + 1;
    }
}
