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

namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TernaryToNullCoalescingFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Use `null` coalescing operator `??` where possible.',
            [
                new CodeSample(
                    "<?php\n\$sample = isset(\$a) ? \$a : \$b;\n",
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before AssignNullCoalescingToCoalesceEqualFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_ISSET);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $issetIndices = array_keys($tokens->findGivenKind(\T_ISSET));

        foreach (array_reverse($issetIndices) as $issetIndex) {
            $this->fixIsset($tokens, $issetIndex);
        }
    }

    /**
     * @param int $index of `T_ISSET` token
     */
    private function fixIsset(Tokens $tokens, int $index): void
    {
        $prevTokenIndex = $tokens->getPrevMeaningfulToken($index);

        if ($this->isHigherPrecedenceAssociativityOperator($tokens[$prevTokenIndex])) {
            return;
        }

        $startBraceIndex = $tokens->getNextTokenOfKind($index, ['(']);
        $endBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $startBraceIndex);

        $ternaryQuestionMarkIndex = $tokens->getNextMeaningfulToken($endBraceIndex);

        if (!$tokens[$ternaryQuestionMarkIndex]->equals('?')) {
            return; // we are not in a ternary operator
        }

        // search what is inside the isset()
        $issetTokens = $this->getMeaningfulSequence($tokens, $startBraceIndex, $endBraceIndex);

        if ($this->hasChangingContent($issetTokens)) {
            return; // some weird stuff inside the isset
        }

        $issetCode = $issetTokens->generateCode();

        if ('$this' === $issetCode) {
            return; // null coalescing operator does not with $this
        }

        // search what is inside the middle argument of ternary operator
        $ternaryColonIndex = $tokens->getNextTokenOfKind($ternaryQuestionMarkIndex, [':']);
        $ternaryFirstOperandTokens = $this->getMeaningfulSequence($tokens, $ternaryQuestionMarkIndex, $ternaryColonIndex);

        if ($issetCode !== $ternaryFirstOperandTokens->generateCode()) {
            return; // regardless of non-meaningful tokens, the operands are different
        }

        $ternaryFirstOperandIndex = $tokens->getNextMeaningfulToken($ternaryQuestionMarkIndex);

        // preserve comments and spaces
        $comments = [];
        $commentStarted = false;

        for ($loopIndex = $index; $loopIndex < $ternaryFirstOperandIndex; ++$loopIndex) {
            if ($tokens[$loopIndex]->isComment()) {
                $comments[] = $tokens[$loopIndex];
                $commentStarted = true;
            } elseif ($commentStarted) {
                if ($tokens[$loopIndex]->isWhitespace()) {
                    $comments[] = $tokens[$loopIndex];
                }

                $commentStarted = false;
            }
        }

        $tokens[$ternaryColonIndex] = new Token([\T_COALESCE, '??']);
        $tokens->overrideRange($index, $ternaryFirstOperandIndex - 1, $comments);
    }

    /**
     * Get the sequence of meaningful tokens and returns a new Tokens instance.
     *
     * @param int $start start index
     * @param int $end   end index
     */
    private function getMeaningfulSequence(Tokens $tokens, int $start, int $end): Tokens
    {
        $sequence = [];
        $index = $start;

        while ($index < $end) {
            $index = $tokens->getNextMeaningfulToken($index);

            if ($index >= $end || null === $index) {
                break;
            }

            $sequence[] = $tokens[$index];
        }

        return Tokens::fromArray($sequence);
    }

    /**
     * Check if the requested token is an operator computed
     * before the ternary operator along with the `isset()`.
     */
    private function isHigherPrecedenceAssociativityOperator(Token $token): bool
    {
        return
            $token->isGivenKind([
                \T_ARRAY_CAST,
                \T_BOOLEAN_AND,
                \T_BOOLEAN_OR,
                \T_BOOL_CAST,
                \T_COALESCE,
                \T_DEC,
                \T_DOUBLE_CAST,
                \T_INC,
                \T_INT_CAST,
                \T_IS_EQUAL,
                \T_IS_GREATER_OR_EQUAL,
                \T_IS_IDENTICAL,
                \T_IS_NOT_EQUAL,
                \T_IS_NOT_IDENTICAL,
                \T_IS_SMALLER_OR_EQUAL,
                \T_OBJECT_CAST,
                \T_POW,
                \T_SL,
                \T_SPACESHIP,
                \T_SR,
                \T_STRING_CAST,
                \T_UNSET_CAST,
            ])
            || $token->equalsAny([
                '!',
                '%',
                '&',
                '*',
                '+',
                '-',
                '/',
                ':',
                '^',
                '|',
                '~',
                '.',
            ]);
    }

    /**
     * Check if the `isset()` content may change if called multiple times.
     *
     * @param Tokens $tokens The original token list
     */
    private function hasChangingContent(Tokens $tokens): bool
    {
        foreach ($tokens as $token) {
            if ($token->isGivenKind([
                \T_DEC,
                \T_INC,
                \T_YIELD,
                \T_YIELD_FROM,
            ]) || $token->equals('(')) {
                return true;
            }
        }

        return false;
    }
}
