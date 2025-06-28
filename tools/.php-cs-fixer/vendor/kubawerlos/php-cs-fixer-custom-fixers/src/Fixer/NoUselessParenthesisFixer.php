<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\BlocksAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class NoUselessParenthesisFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no useless parentheses.',
            [
                new CodeSample('<?php
foo(($bar));
'),
            ],
            '',
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(['(', CT::T_BRACE_CLASS_INSTANTIATION_OPEN]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0; $index < $tokens->count(); $index++) {
            if (!$tokens[$index]->equalsAny(['(', [CT::T_BRACE_CLASS_INSTANTIATION_OPEN]])) {
                continue;
            }

            /** @var array{type: Tokens::BLOCK_TYPE_*, isStart: bool} $blockType */
            $blockType = Tokens::detectBlockType($tokens[$index]);
            $blockEndIndex = $tokens->findBlockEnd($blockType['type'], $index);

            if (!$this->isBlockToRemove($tokens, $index, $blockEndIndex)) {
                continue;
            }

            $this->clearWhitespace($tokens, $index + 1);
            $this->clearWhitespace($tokens, $blockEndIndex - 1);
            $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            $tokens->clearTokenAndMergeSurroundingWhitespace($blockEndIndex);

            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            \assert(\is_int($prevIndex));

            if ($tokens[$prevIndex]->isGivenKind([\T_RETURN, \T_THROW])) {
                $tokens->ensureWhitespaceAtIndex($prevIndex + 1, 0, ' ');
            }
        }
    }

    private function isBlockToRemove(Tokens $tokens, int $startIndex, int $endIndex): bool
    {
        if ($this->isParenthesisBlockInside($tokens, $startIndex, $endIndex)) {
            return true;
        }

        $prevStartIndex = $tokens->getPrevMeaningfulToken($startIndex);
        \assert(\is_int($prevStartIndex));
        $nextEndIndex = $tokens->getNextMeaningfulToken($endIndex);
        \assert(\is_int($nextEndIndex));

        if ((new BlocksAnalyzer())->isBlock($tokens, $prevStartIndex, $nextEndIndex)) {
            return true;
        }

        if ($tokens[$nextEndIndex]->equalsAny(['(', '{', [\T_DOUBLE_ARROW], [CT::T_USE_LAMBDA], [CT::T_TYPE_COLON]])) {
            return false;
        }

        if ($this->isForbiddenBeforeOpenParenthesis($tokens, $prevStartIndex)) {
            return false;
        }

        if ($this->isExpressionInside($tokens, $startIndex, $endIndex)) {
            return true;
        }

        if ($this->hasLowPrecedenceLogicOperator($tokens, $startIndex, $endIndex)) {
            return false;
        }

        return $tokens[$prevStartIndex]->equalsAny(['=', [\T_RETURN], [\T_THROW]]) && $tokens[$nextEndIndex]->equals(';');
    }

    private function isForbiddenBeforeOpenParenthesis(Tokens $tokens, int $index): bool
    {
        if (
            $tokens[$index]->isGivenKind([
                \T_ARRAY,
                \T_CLASS,
                \T_ELSEIF,
                \T_EMPTY,
                \T_EVAL,
                \T_EXIT,
                \T_HALT_COMPILER,
                \T_IF,
                \T_ISSET,
                \T_LIST,
                \T_STATIC,
                \T_STRING,
                \T_SWITCH,
                \T_UNSET,
                \T_VARIABLE,
                \T_WHILE,
                CT::T_CLASS_CONSTANT,
            ])
        ) {
            return true;
        }

        /** @var null|array{isStart: bool, type: int} $blockType */
        $blockType = Tokens::detectBlockType($tokens[$index]);

        return $blockType !== null && !$blockType['isStart'];
    }

    private function isParenthesisBlockInside(Tokens $tokens, int $startIndex, int $endIndex): bool
    {
        $nextStartIndex = $tokens->getNextMeaningfulToken($startIndex);
        \assert(\is_int($nextStartIndex));

        if (!$tokens[$nextStartIndex]->equalsAny(['(', [CT::T_BRACE_CLASS_INSTANTIATION_OPEN]])) {
            return false;
        }

        $prevIndex = $tokens->getPrevMeaningfulToken($endIndex);
        \assert(\is_int($prevIndex));

        return (new BlocksAnalyzer())->isBlock($tokens, $nextStartIndex, $prevIndex);
    }

    private function isExpressionInside(Tokens $tokens, int $startIndex, int $endIndex): bool
    {
        $index = $tokens->getNextMeaningfulToken($startIndex);
        \assert(\is_int($index));

        while ($index < $endIndex) {
            if (
                !$tokens[$index]->isGivenKind([
                    \T_CONSTANT_ENCAPSED_STRING,
                    \T_DNUMBER,
                    \T_DOUBLE_COLON,
                    \T_LNUMBER,
                    \T_OBJECT_OPERATOR,
                    \T_STRING,
                    \T_VARIABLE,
                ]) && !$tokens[$index]->isMagicConstant()
            ) {
                return false;
            }

            $index = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($index));
        }

        return true;
    }

    private function hasLowPrecedenceLogicOperator(Tokens $tokens, int $startIndex, int $endIndex): bool
    {
        $index = $tokens->getNextMeaningfulToken($startIndex);
        \assert(\is_int($index));

        while ($index < $endIndex) {
            if (
                $tokens[$index]->isGivenKind([
                    \T_LOGICAL_XOR,
                    \T_LOGICAL_AND,
                    \T_LOGICAL_OR,
                ])
            ) {
                return true;
            }

            $index = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($index));
        }

        return false;
    }

    private function clearWhitespace(Tokens $tokens, int $index): void
    {
        if (!$tokens[$index]->isWhitespace()) {
            return;
        }

        $prevIndex = $tokens->getNonEmptySibling($index, -1);
        \assert(\is_int($prevIndex));

        if ($tokens[$prevIndex]->isComment()) {
            $tokens->ensureWhitespaceAtIndex($index, 0, \rtrim($tokens[$index]->getContent(), " \t"));

            return;
        }

        $tokens->clearAt($index);
    }
}
