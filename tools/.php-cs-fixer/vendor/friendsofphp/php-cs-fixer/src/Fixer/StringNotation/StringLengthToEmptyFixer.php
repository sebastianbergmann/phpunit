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

namespace PhpCsFixer\Fixer\StringNotation;

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class StringLengthToEmptyFixer extends AbstractFunctionReferenceFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'String tests for empty must be done against `\'\'`, not with `strlen`.',
            [new CodeSample("<?php \$a = 0 === strlen(\$b) || \\STRLEN(\$c) < 1;\n")],
            null,
            'Risky when `strlen` is overridden, when called using a `stringable` object, also no longer triggers warning when called using non-string(able).',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before NoExtraBlankLinesFixer, NoTrailingWhitespaceFixer.
     * Must run after NoSpacesInsideParenthesisFixer, SpacesInsideParenthesesFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        foreach ($this->findStrLengthCalls($tokens) as $candidate) {
            [$functionNameIndex, $openParenthesisIndex, $closeParenthesisIndex] = $candidate;
            $arguments = $argumentsAnalyzer->getArguments($tokens, $openParenthesisIndex, $closeParenthesisIndex);

            if (1 !== \count($arguments)) {
                continue; // must be one argument
            }

            // test for leading `\` before `strlen` call

            $nextIndex = $tokens->getNextMeaningfulToken($closeParenthesisIndex);
            $previousIndex = $tokens->getPrevMeaningfulToken($functionNameIndex);

            if ($tokens[$previousIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                $namespaceSeparatorIndex = $previousIndex;
                $previousIndex = $tokens->getPrevMeaningfulToken($previousIndex);
            } else {
                $namespaceSeparatorIndex = null;
            }

            // test for yoda vs non-yoda fix case

            if ($this->isOperatorOfInterest($tokens[$previousIndex])) { // test if valid yoda case to fix
                $operatorIndex = $previousIndex;
                $operandIndex = $tokens->getPrevMeaningfulToken($previousIndex);

                if (!$this->isOperandOfInterest($tokens[$operandIndex])) { // test if operand is `0` or `1`
                    continue;
                }

                $replacement = $this->getReplacementYoda($tokens[$operatorIndex], $tokens[$operandIndex]);

                if (null === $replacement) {
                    continue;
                }

                if ($this->isOfHigherPrecedence($tokens[$nextIndex])) { // is of higher precedence right; continue
                    continue;
                }

                if ($this->isOfHigherPrecedence($tokens[$tokens->getPrevMeaningfulToken($operandIndex)])) { // is of higher precedence left; continue
                    continue;
                }
            } elseif ($this->isOperatorOfInterest($tokens[$nextIndex])) { // test if valid !yoda case to fix
                $operatorIndex = $nextIndex;
                $operandIndex = $tokens->getNextMeaningfulToken($nextIndex);

                if (!$this->isOperandOfInterest($tokens[$operandIndex])) { // test if operand is `0` or `1`
                    continue;
                }

                $replacement = $this->getReplacementNotYoda($tokens[$operatorIndex], $tokens[$operandIndex]);

                if (null === $replacement) {
                    continue;
                }

                if ($this->isOfHigherPrecedence($tokens[$tokens->getNextMeaningfulToken($operandIndex)])) { // is of higher precedence right; continue
                    continue;
                }

                if ($this->isOfHigherPrecedence($tokens[$previousIndex])) { // is of higher precedence left; continue
                    continue;
                }
            } else {
                continue;
            }

            // prepare for fixing

            $keepParentheses = $this->keepParentheses($tokens, $openParenthesisIndex, $closeParenthesisIndex);

            if (\T_IS_IDENTICAL === $replacement) {
                $operandContent = '===';
            } else { // T_IS_NOT_IDENTICAL === $replacement
                $operandContent = '!==';
            }

            // apply fixing

            $tokens[$operandIndex] = new Token([\T_CONSTANT_ENCAPSED_STRING, "''"]);
            $tokens[$operatorIndex] = new Token([$replacement, $operandContent]);

            if (!$keepParentheses) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($closeParenthesisIndex);
                $tokens->clearTokenAndMergeSurroundingWhitespace($openParenthesisIndex);
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($functionNameIndex);

            if (null !== $namespaceSeparatorIndex) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($namespaceSeparatorIndex);
            }
        }
    }

    private function getReplacementYoda(Token $operator, Token $operand): ?int
    {
        /* Yoda 0

        0 === strlen($b) | '' === $b
        0 !== strlen($b) | '' !== $b
        0 <= strlen($b)  | X         makes no sense, assume overridden
        0 >= strlen($b)  | '' === $b
        0 < strlen($b)   | '' !== $b
        0 > strlen($b)   | X         makes no sense, assume overridden
        */

        if ('0' === $operand->getContent()) {
            if ($operator->isGivenKind([\T_IS_IDENTICAL, \T_IS_GREATER_OR_EQUAL])) {
                return \T_IS_IDENTICAL;
            }

            if ($operator->isGivenKind(\T_IS_NOT_IDENTICAL) || $operator->equals('<')) {
                return \T_IS_NOT_IDENTICAL;
            }

            return null;
        }

        /* Yoda 1

        1 === strlen($b) | X         cannot simplify
        1 !== strlen($b) | X         cannot simplify
        1 <= strlen($b)  | '' !== $b
        1 >= strlen($b)  |           cannot simplify
        1 < strlen($b)   |           cannot simplify
        1 > strlen($b)   | '' === $b
        */

        if ($operator->isGivenKind(\T_IS_SMALLER_OR_EQUAL)) {
            return \T_IS_NOT_IDENTICAL;
        }

        if ($operator->equals('>')) {
            return \T_IS_IDENTICAL;
        }

        return null;
    }

    private function getReplacementNotYoda(Token $operator, Token $operand): ?int
    {
        /* Not Yoda 0

        strlen($b) === 0 | $b === ''
        strlen($b) !== 0 | $b !== ''
        strlen($b) <= 0  | $b === ''
        strlen($b) >= 0  | X         makes no sense, assume overridden
        strlen($b) < 0   | X         makes no sense, assume overridden
        strlen($b) > 0   | $b !== ''
        */

        if ('0' === $operand->getContent()) {
            if ($operator->isGivenKind([\T_IS_IDENTICAL, \T_IS_SMALLER_OR_EQUAL])) {
                return \T_IS_IDENTICAL;
            }

            if ($operator->isGivenKind(\T_IS_NOT_IDENTICAL) || $operator->equals('>')) {
                return \T_IS_NOT_IDENTICAL;
            }

            return null;
        }

        /* Not Yoda 1

        strlen($b) === 1 | X         cannot simplify
        strlen($b) !== 1 | X         cannot simplify
        strlen($b) <= 1  | X         cannot simplify
        strlen($b) >= 1  | $b !== ''
        strlen($b) < 1   | $b === ''
        strlen($b) > 1   | X         cannot simplify
        */

        if ($operator->isGivenKind(\T_IS_GREATER_OR_EQUAL)) {
            return \T_IS_NOT_IDENTICAL;
        }

        if ($operator->equals('<')) {
            return \T_IS_IDENTICAL;
        }

        return null;
    }

    private function isOperandOfInterest(Token $token): bool
    {
        if (!$token->isGivenKind(\T_LNUMBER)) {
            return false;
        }

        $content = $token->getContent();

        return '0' === $content || '1' === $content;
    }

    private function isOperatorOfInterest(Token $token): bool
    {
        return
            $token->isGivenKind([\T_IS_IDENTICAL, \T_IS_NOT_IDENTICAL, \T_IS_SMALLER_OR_EQUAL, \T_IS_GREATER_OR_EQUAL])
            || $token->equals('<') || $token->equals('>');
    }

    private function isOfHigherPrecedence(Token $token): bool
    {
        return $token->isGivenKind([\T_INSTANCEOF, \T_POW, \T_SL, \T_SR]) || $token->equalsAny([
            '!',
            '%',
            '*',
            '+',
            '-',
            '.',
            '/',
            '~',
            '?',
        ]);
    }

    private function keepParentheses(Tokens $tokens, int $openParenthesisIndex, int $closeParenthesisIndex): bool
    {
        $i = $tokens->getNextMeaningfulToken($openParenthesisIndex);

        if ($tokens[$i]->isCast()) {
            $i = $tokens->getNextMeaningfulToken($i);
        }

        for (; $i < $closeParenthesisIndex; ++$i) {
            $token = $tokens[$i];

            if ($token->isGivenKind([\T_VARIABLE, \T_STRING]) || $token->isObjectOperator() || $token->isWhitespace() || $token->isComment()) {
                continue;
            }

            $blockType = Tokens::detectBlockType($token);

            if (null !== $blockType && $blockType['isStart']) {
                $i = $tokens->findBlockEnd($blockType['type'], $i);

                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return iterable<array{int, int, int}>
     */
    private function findStrLengthCalls(Tokens $tokens): iterable
    {
        $candidates = [];
        $count = \count($tokens);

        for ($i = 0; $i < $count; ++$i) {
            $candidate = $this->find('strlen', $tokens, $i, $count);

            if (null === $candidate) {
                break;
            }

            $i = $candidate[1]; // proceed to openParenthesisIndex
            $candidates[] = $candidate;
        }

        foreach (array_reverse($candidates) as $candidate) {
            yield $candidate;
        }
    }
}
