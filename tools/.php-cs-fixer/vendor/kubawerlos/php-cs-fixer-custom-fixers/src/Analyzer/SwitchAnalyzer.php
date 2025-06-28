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

use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\Analyzer\Analysis\CaseAnalysis;
use PhpCsFixerCustomFixers\Analyzer\Analysis\SwitchAnalysis;

/**
 * @internal
 */
final class SwitchAnalyzer
{
    public function getSwitchAnalysis(Tokens $tokens, int $switchIndex): SwitchAnalysis
    {
        if (!$tokens[$switchIndex]->isGivenKind(\T_SWITCH)) {
            throw new \InvalidArgumentException(\sprintf('Index %d is not "switch".', $switchIndex));
        }

        $casesStartIndex = $this->getCasesStart($tokens, $switchIndex);
        $casesEndIndex = $this->getCasesEnd($tokens, $casesStartIndex);

        $cases = [];
        $index = $casesStartIndex;
        while ($index < $casesEndIndex) {
            $index = $this->getNextSameLevelToken($tokens, $index);

            if (!$tokens[$index]->isGivenKind([\T_CASE, \T_DEFAULT])) {
                continue;
            }

            $caseAnalysis = $this->getCaseAnalysis($tokens, $index);

            $cases[] = $caseAnalysis;
        }

        return new SwitchAnalysis($casesStartIndex, $casesEndIndex, $cases);
    }

    private function getCasesStart(Tokens $tokens, int $switchIndex): int
    {
        $parenthesisStartIndex = $tokens->getNextMeaningfulToken($switchIndex);
        \assert(\is_int($parenthesisStartIndex));
        $parenthesisEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $parenthesisStartIndex);

        $casesStartIndex = $tokens->getNextMeaningfulToken($parenthesisEndIndex);
        \assert(\is_int($casesStartIndex));

        return $casesStartIndex;
    }

    private function getCasesEnd(Tokens $tokens, int $casesStartIndex): int
    {
        if ($tokens[$casesStartIndex]->equals('{')) {
            return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $casesStartIndex);
        }

        $index = $casesStartIndex;
        while ($index < $tokens->count()) {
            $index = $this->getNextSameLevelToken($tokens, $index);

            if ($tokens[$index]->isGivenKind(\T_ENDSWITCH)) {
                break;
            }
        }

        $afterEndswitchIndex = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($afterEndswitchIndex));

        return $tokens[$afterEndswitchIndex]->equals(';') ? $afterEndswitchIndex : $index;
    }

    private function getCaseAnalysis(Tokens $tokens, int $index): CaseAnalysis
    {
        while ($index < $tokens->count()) {
            $index = $this->getNextSameLevelToken($tokens, $index);

            if ($tokens[$index]->equalsAny([':', ';'])) {
                break;
            }
        }

        return new CaseAnalysis($index);
    }

    private function getNextSameLevelToken(Tokens $tokens, int $index): int
    {
        $index = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($index));

        if ($tokens[$index]->isGivenKind(\T_SWITCH)) {
            return (new self())->getSwitchAnalysis($tokens, $index)->getCasesEnd();
        }

        $blockType = Tokens::detectBlockType($tokens[$index]);
        if ($blockType !== null && $blockType['isStart']) {
            return $tokens->findBlockEnd($blockType['type'], $index) + 1;
        }

        return $index;
    }
}
