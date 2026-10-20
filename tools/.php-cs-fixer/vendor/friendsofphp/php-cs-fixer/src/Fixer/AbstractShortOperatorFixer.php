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

namespace PhpCsFixer\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tokenizer\Analyzer\AlternativeSyntaxAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\RangeAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractShortOperatorFixer extends AbstractFixer
{
    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $alternativeSyntaxAnalyzer = new AlternativeSyntaxAnalyzer();

        for ($index = \count($tokens) - 1; $index > 3; --$index) {
            if (!$this->isOperatorTokenCandidate($tokens, $index)) {
                continue;
            }

            // get what is before the operator

            $beforeRange = $this->getBeforeOperatorRange($tokens, $index);
            $equalsIndex = $tokens->getPrevMeaningfulToken($beforeRange['start']);

            // make sure that before that is '='

            if (!$tokens[$equalsIndex]->equals('=')) {
                continue;
            }

            // get and check what is before '='

            $assignRange = $this->getBeforeOperatorRange($tokens, $equalsIndex);
            $beforeAssignmentIndex = $tokens->getPrevMeaningfulToken($assignRange['start']);

            if ($tokens[$beforeAssignmentIndex]->equals(':')) {
                if (!$this->belongsToSwitchOrAlternativeSyntax($alternativeSyntaxAnalyzer, $tokens, $beforeAssignmentIndex)) {
                    continue;
                }
            } elseif (!$tokens[$beforeAssignmentIndex]->equalsAny([';', '{', '}', '(', ')', ',', [\T_OPEN_TAG], [\T_RETURN]])) {
                continue;
            }

            // check if "assign" and "before" the operator are (functionally) the same

            if (RangeAnalyzer::rangeEqualsRange($tokens, $assignRange, $beforeRange)) {
                $this->shortenOperation($tokens, $equalsIndex, $index, $assignRange, $beforeRange);

                continue;
            }

            if (!$this->isOperatorCommutative($tokens[$index])) {
                continue;
            }

            $afterRange = $this->getAfterOperatorRange($tokens, $index);

            // check if "assign" and "after" the operator are (functionally) the same
            if (!RangeAnalyzer::rangeEqualsRange($tokens, $assignRange, $afterRange)) {
                continue;
            }

            $this->shortenOperation($tokens, $equalsIndex, $index, $assignRange, $afterRange);
        }
    }

    abstract protected function getReplacementToken(Token $token): Token;

    abstract protected function isOperatorTokenCandidate(Tokens $tokens, int $index): bool;

    /**
     * @param array{start: int, end: int} $assignRange
     * @param array{start: int, end: int} $operatorRange
     */
    private function shortenOperation(
        Tokens $tokens,
        int $equalsIndex,
        int $operatorIndex,
        array $assignRange,
        array $operatorRange
    ): void {
        $tokens[$equalsIndex] = $this->getReplacementToken($tokens[$operatorIndex]);
        $tokens->clearTokenAndMergeSurroundingWhitespace($operatorIndex);
        $this->clearMeaningfulFromRange($tokens, $operatorRange);

        foreach ([$equalsIndex, $assignRange['end']] as $i) {
            $i = $tokens->getNonEmptySibling($i, 1);

            if ($tokens[$i]->isWhitespace(" \t")) {
                $tokens[$i] = new Token([\T_WHITESPACE, ' ']);
            } elseif (!$tokens[$i]->isWhitespace()) {
                $tokens->insertAt($i, new Token([\T_WHITESPACE, ' ']));
            }
        }
    }

    /**
     * @return array{start: int, end: int}
     */
    private function getAfterOperatorRange(Tokens $tokens, int $index): array
    {
        $index = $tokens->getNextMeaningfulToken($index);
        $range = ['start' => $index];

        while (true) {
            $nextIndex = $tokens->getNextMeaningfulToken($index);

            if (null === $nextIndex || $tokens[$nextIndex]->equalsAny([';', ',', [\T_CLOSE_TAG]])) {
                break;
            }

            $blockType = Tokens::detectBlockType($tokens[$nextIndex]);

            if (null === $blockType) {
                $index = $nextIndex;

                continue;
            }

            if (false === $blockType['isStart']) {
                break;
            }

            $index = $tokens->findBlockEnd($blockType['type'], $nextIndex);
        }

        $range['end'] = $index;

        return $range;
    }

    /**
     * @return array{start: int, end: int}
     */
    private function getBeforeOperatorRange(Tokens $tokens, int $index): array
    {
        static $blockOpenTypes;

        if (null === $blockOpenTypes) {
            $blockOpenTypes = [',']; // not a true "block type", but speeds up things

            foreach (Tokens::getBlockEdgeDefinitions() as $definition) {
                $blockOpenTypes[] = $definition['start'];
            }
        }

        $controlStructureWithoutBracesTypes = [\T_IF, \T_ELSE, \T_ELSEIF, \T_FOR, \T_FOREACH, \T_WHILE];

        $previousIndex = $tokens->getPrevMeaningfulToken($index);
        $previousToken = $tokens[$previousIndex];

        if ($tokens[$previousIndex]->equalsAny($blockOpenTypes)) {
            return ['start' => $index, 'end' => $index];
        }

        $range = ['end' => $previousIndex];
        $index = $previousIndex;

        while ($previousToken->equalsAny([
            '$',
            ']',
            ')',
            [CT::T_ARRAY_INDEX_BRACE_CLOSE],
            [CT::T_DYNAMIC_PROP_BRACE_CLOSE],
            [CT::T_DYNAMIC_VAR_BRACE_CLOSE],
            [\T_NS_SEPARATOR],
            [\T_STRING],
            [\T_VARIABLE],
        ])) {
            $blockType = Tokens::detectBlockType($previousToken);

            if (null !== $blockType) {
                $blockStart = $tokens->findBlockStart($blockType['type'], $previousIndex);

                if ($tokens[$previousIndex]->equals(')') && $tokens[$tokens->getPrevMeaningfulToken($blockStart)]->isGivenKind($controlStructureWithoutBracesTypes)) {
                    break; // we went too far back
                }

                $previousIndex = $blockStart;
            }

            $index = $previousIndex;
            $previousIndex = $tokens->getPrevMeaningfulToken($previousIndex);
            $previousToken = $tokens[$previousIndex];
        }

        if ($previousToken->isGivenKind(\T_OBJECT_OPERATOR)) {
            $index = $this->getBeforeOperatorRange($tokens, $previousIndex)['start'];
        } elseif ($previousToken->isGivenKind(\T_PAAMAYIM_NEKUDOTAYIM)) {
            $index = $this->getBeforeOperatorRange($tokens, $tokens->getPrevMeaningfulToken($previousIndex))['start'];
        }

        $range['start'] = $index;

        return $range;
    }

    /**
     * @param array{start: int, end: int} $range
     */
    private function clearMeaningfulFromRange(Tokens $tokens, array $range): void
    {
        // $range['end'] must be meaningful!
        for ($i = $range['end']; $i >= $range['start']; $i = $tokens->getPrevMeaningfulToken($i)) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($i);
        }
    }

    private function isOperatorCommutative(Token $operatorToken): bool
    {
        if ($operatorToken->isGivenKind(\T_COALESCE)) {
            return false;
        }

        // check for commutative kinds
        if ($operatorToken->equalsAny(['*', '|', '&', '^'])) { // note that for arrays in PHP `+` is not commutative
            return true;
        }

        // check for non-commutative kinds
        if ($operatorToken->equalsAny(['-', '/', '.', '%', '+'])) {
            return false;
        }

        throw new \InvalidArgumentException(\sprintf('Not supported operator "%s".', $operatorToken->toJson()));
    }

    private function belongsToSwitchOrAlternativeSyntax(AlternativeSyntaxAnalyzer $alternativeSyntaxAnalyzer, Tokens $tokens, int $index): bool
    {
        $candidate = $index;
        $index = $tokens->getPrevMeaningfulToken($candidate);

        if ($tokens[$index]->isGivenKind(\T_DEFAULT)) {
            return true;
        }

        $index = $tokens->getPrevMeaningfulToken($index);

        if ($tokens[$index]->isGivenKind(\T_CASE)) {
            return true;
        }

        return $alternativeSyntaxAnalyzer->belongsToAlternativeSyntax($tokens, $candidate);
    }
}
