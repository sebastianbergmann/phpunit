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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class ClassConstantUsageFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Class constant must be used instead of a copy of string.',
            [new CodeSample(
                <<<'PHP'
                    <?php
                    class Foo
                    {
                        public const BAR = 'bar';
                        public function bar()
                        {
                            return 'bar';
                        }
                    }

                    PHP,
            )],
            '',
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_CONSTANT_ENCAPSED_STRING]);
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

            $this->fixClass($tokens, $openParenthesisIndex, $closeParenthesisIndex);
        }
    }

    private function fixClass(Tokens $tokens, int $openParenthesisIndex, int $closeParenthesisIndex): void
    {
        [$constantsMap, $constantsIndices] = $this->getClassConstants($tokens, $openParenthesisIndex, $closeParenthesisIndex);

        for ($index = $closeParenthesisIndex; $index > $openParenthesisIndex; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
                continue;
            }

            if (!isset($constantsMap[$tokens[$index]->getContent()])) {
                continue;
            }

            if (isset($constantsIndices[$index])) {
                continue;
            }

            $tokens->overrideRange(
                $index,
                $index,
                [
                    new Token([\T_STRING, 'self']),
                    new Token([\T_DOUBLE_COLON, '::']),
                    new Token([\T_STRING, $constantsMap[$tokens[$index]->getContent()]]),
                ],
            );
        }
    }

    /**
     * @return array{array<string, string>, array<int, true>}
     */
    private function getClassConstants(Tokens $tokens, int $openParenthesisIndex, int $closeParenthesisIndex): array
    {
        $constants = [];
        $constantsIndices = [];
        for ($index = $openParenthesisIndex; $index < $closeParenthesisIndex; $index++) {
            if (!$tokens[$index]->isGivenKind(\T_CONST)) {
                continue;
            }

            $assignTokenIndex = $tokens->getNextTokenOfKind($index, ['=']);
            \assert(\is_int($assignTokenIndex));

            $constantNameIndex = $tokens->getPrevMeaningfulToken($assignTokenIndex);
            \assert(\is_int($constantNameIndex));

            $constantValueIndex = $tokens->getNextMeaningfulToken($assignTokenIndex);
            \assert(\is_int($constantValueIndex));

            $constantsIndices[$constantValueIndex] = true;

            if (!$tokens[$constantValueIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
                continue;
            }

            $constants[$tokens[$constantNameIndex]->getContent()] = $tokens[$constantValueIndex]->getContent();
        }

        return [$this->getClassConstantsMap($constants), $constantsIndices];
    }

    /**
     * @param array<string, string> $constants
     *
     * @return array<string, string>
     */
    private function getClassConstantsMap(array $constants): array
    {
        $map = [];
        $valuesCount = [];

        foreach ($constants as $name => $value) {
            $map[$value] = $name;
            $valuesCount[$value] = ($valuesCount[$value] ?? 0) + 1;

            if ($valuesCount[$value] > 1) {
                unset($map[$value]);
            }
        }

        return $map;
    }
}
