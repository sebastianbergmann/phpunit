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

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class TypedClassConstantFixer extends AbstractFixer
{
    private const INTEGER_KINDS = [\T_LNUMBER, '+', '-', '*', '(', ')', \T_POW, \T_SL, \T_SR, \T_LINE];
    private const FLOAT_KINDS = [\T_DNUMBER, ...self::INTEGER_KINDS, '/'];
    private const STRING_KINDS = [\T_CONSTANT_ENCAPSED_STRING, \T_START_HEREDOC, \T_ENCAPSED_AND_WHITESPACE, \T_END_HEREDOC, \T_LNUMBER, \T_DNUMBER, \T_CLASS_C, \T_DIR, \T_FILE, \T_FUNC_C, \T_METHOD_C, \T_NS_C, \T_TRAIT_C];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Class constants must have a type.',
            [
                new VersionSpecificCodeSample(
                    <<<'PHP'
                        <?php
                        class Foo {
                            public const MAX_VALUE_OF_SOMETHING = 42;
                            public const THE_NAME_OF_SOMEONE = 'John Doe';
                        }

                        PHP,
                    new VersionSpecification(80300),
                ),
            ],
        );
    }

    /**
     * Must run after SingleClassElementPerStatementFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_CONST]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_CLASS)) {
                continue;
            }

            $openParenthesisIndex = $tokens->getNextTokenOfKind($index, ['{']);
            \assert(\is_int($openParenthesisIndex));

            $closeParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $openParenthesisIndex);

            self::fixClass($tokens, $openParenthesisIndex, $closeParenthesisIndex);
        }
    }

    private static function fixClass(Tokens $tokens, int $openParenthesisIndex, int $closeParenthesisIndex): void
    {
        for ($index = $closeParenthesisIndex; $index > $openParenthesisIndex; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_CONST)) {
                continue;
            }

            $constantNameIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($constantNameIndex));

            $assignmentIndex = $tokens->getNextMeaningfulToken($constantNameIndex);
            \assert(\is_int($assignmentIndex));

            if (!$tokens[$assignmentIndex]->equals('=')) {
                continue;
            }

            $expressionStartIndex = $tokens->getNextMeaningfulToken($assignmentIndex);
            \assert(\is_int($expressionStartIndex));

            if ($tokens[$expressionStartIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                $expressionStartIndex = $tokens->getNextMeaningfulToken($expressionStartIndex);
                \assert(\is_int($expressionStartIndex));
            }

            $type = self::getTypeOfExpression($tokens, $expressionStartIndex);

            $tokens->insertAt(
                $constantNameIndex,
                [
                    new Token([$type === 'array' ? CT::T_ARRAY_TYPEHINT : \T_STRING, $type]),
                    new Token([\T_WHITESPACE, ' ']),
                ],
            );
        }
    }

    private static function getTypeOfExpression(Tokens $tokens, int $index): string
    {
        $semicolonIndex = $tokens->getNextTokenOfKind($index, [';']);
        \assert(\is_int($semicolonIndex));

        $beforeSemicolonIndex = $tokens->getPrevMeaningfulToken($semicolonIndex);
        \assert(\is_int($beforeSemicolonIndex));

        $foundKinds = [];

        $questionMarkCount = 0;
        do {
            if ($questionMarkCount > 1) {
                return 'mixed';
            }
            $kind = $tokens[$index]->getId() ?? $tokens[$index]->getContent();
            if ($kind === '?') {
                $questionMarkCount++;
                $foundKinds = [];
                continue;
            }
            $foundKinds[] = $kind;

            $index = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($index));
        } while ($index < $semicolonIndex);

        if ($foundKinds === [\T_STRING]) {
            $lowercasedContent = \strtolower($tokens[$beforeSemicolonIndex]->getContent());
            if (\in_array($lowercasedContent, ['false', 'true', 'null'], true)) {
                return $lowercasedContent;
            }
        }

        return self::getTypeOfExpressionForTokenKinds($foundKinds);
    }

    /**
     * @param list<int|string> $tokenKinds
     */
    private static function getTypeOfExpressionForTokenKinds(array $tokenKinds): string
    {
        if (self::isOfTypeBasedOnKinds($tokenKinds, [], [\T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
            return 'array';
        }

        if (self::isOfTypeBasedOnKinds($tokenKinds, self::INTEGER_KINDS, [])) {
            return 'int';
        }

        if (self::isOfTypeBasedOnKinds($tokenKinds, self::FLOAT_KINDS, [])) {
            return 'float';
        }

        if (self::isOfTypeBasedOnKinds($tokenKinds, self::STRING_KINDS, ['.', CT::T_CLASS_CONSTANT])) {
            return 'string';
        }

        return 'mixed';
    }

    /**
     * @param list<int|string> $expressionTokenKinds
     * @param list<int|string> $expectedKinds
     * @param list<int|string> $instantWinners
     */
    private static function isOfTypeBasedOnKinds(
        array $expressionTokenKinds,
        array $expectedKinds,
        array $instantWinners
    ): bool {
        foreach ($expressionTokenKinds as $index => $expressionTokenKind) {
            if (\in_array($expressionTokenKind, $instantWinners, true)) {
                return true;
            }
            if (\in_array($expressionTokenKind, $expectedKinds, true)) {
                unset($expressionTokenKinds[$index]);
            }
        }

        return $expressionTokenKinds === [];
    }
}
