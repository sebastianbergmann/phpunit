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

namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SelfStaticAccessorFixer extends AbstractFixer
{
    private const CLASSY_TYPES = [\T_CLASS, FCT::T_ENUM];
    private const CLASSY_TOKENS_OF_INTEREST = [[\T_CLASS], [FCT::T_ENUM]];
    private TokensAnalyzer $tokensAnalyzer;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Inside an enum or `final`/anonymous class, `self` should be preferred over `static`.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class Sample
                        {
                            private static $A = 1;

                            public function getBar()
                            {
                                return static::class.static::test().static::$A;
                            }

                            private static function test()
                            {
                                return 'test';
                            }
                        }

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class Foo
                        {
                            public function bar()
                            {
                                return new static();
                            }
                        }

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class Foo
                        {
                            public function isBar()
                            {
                                return $foo instanceof static;
                            }
                        }

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        $a = new class() {
                            public function getBar()
                            {
                                return static::class;
                            }
                        };

                        PHP,
                ),
                new VersionSpecificCodeSample(
                    <<<'PHP'
                        <?php
                        enum Foo
                        {
                            public const A = 123;

                            public static function bar(): void
                            {
                                echo static::A;
                            }
                        }

                        PHP,
                    new VersionSpecification(8_01_00),
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_STATIC)
            && $tokens->isAnyTokenKindsFound(self::CLASSY_TYPES)
            && $tokens->isAnyTokenKindsFound([\T_DOUBLE_COLON, \T_NEW, \T_INSTANCEOF]);
    }

    /**
     * {@inheritdoc}
     *
     * Must run after FinalClassFixer, FinalInternalClassFixer, FunctionToConstantFixer, PhpUnitTestCaseStaticMethodCallsFixer.
     */
    public function getPriority(): int
    {
        return -10;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->tokensAnalyzer = new TokensAnalyzer($tokens);
        $classyIndex = $tokens->getNextTokenOfKind(0, self::CLASSY_TOKENS_OF_INTEREST);

        while (null !== $classyIndex) {
            if ($tokens[$classyIndex]->isGivenKind(\T_CLASS)) {
                $modifiers = $this->tokensAnalyzer->getClassyModifiers($classyIndex);

                if (
                    isset($modifiers['final'])
                    || $this->tokensAnalyzer->isAnonymousClass($classyIndex)
                ) {
                    $classyIndex = $this->fixClassy($tokens, $classyIndex);
                }
            } else {
                $classyIndex = $this->fixClassy($tokens, $classyIndex);
            }

            $classyIndex = $tokens->getNextTokenOfKind($classyIndex, self::CLASSY_TOKENS_OF_INTEREST);
        }
    }

    private function fixClassy(Tokens $tokens, int $index): int
    {
        $index = $tokens->getNextTokenOfKind($index, ['{']);
        $classOpenCount = 1;

        while ($classOpenCount > 0) {
            ++$index;

            if ($tokens[$index]->equals('{')) {
                ++$classOpenCount;

                continue;
            }

            if ($tokens[$index]->equals('}')) {
                --$classOpenCount;

                continue;
            }

            if ($tokens[$index]->isGivenKind(\T_FUNCTION)) {
                // do not fix inside lambda
                if ($this->tokensAnalyzer->isLambda($index)) {
                    // figure out where the lambda starts
                    $index = $tokens->getNextTokenOfKind($index, ['{']);
                    $openCount = 1;

                    do {
                        $index = $tokens->getNextTokenOfKind($index, ['}', '{', [\T_CLASS]]);
                        if ($tokens[$index]->equals('}')) {
                            --$openCount;
                        } elseif ($tokens[$index]->equals('{')) {
                            ++$openCount;
                        } else {
                            $index = $this->fixClassy($tokens, $index);
                        }
                    } while ($openCount > 0);
                }

                continue;
            }

            if ($tokens[$index]->isGivenKind([\T_NEW, \T_INSTANCEOF])) {
                $index = $tokens->getNextMeaningfulToken($index);

                if ($tokens[$index]->isGivenKind(\T_STATIC)) {
                    $tokens[$index] = new Token([\T_STRING, 'self']);
                }

                continue;
            }

            if (!$tokens[$index]->isGivenKind(\T_STATIC)) {
                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($index);

            if (!$tokens[$nextIndex]->isGivenKind(\T_DOUBLE_COLON)) {
                continue;
            }

            $tokens[$index] = new Token([\T_STRING, 'self']);
            $index = $nextIndex;
        }

        return $index;
    }
}
