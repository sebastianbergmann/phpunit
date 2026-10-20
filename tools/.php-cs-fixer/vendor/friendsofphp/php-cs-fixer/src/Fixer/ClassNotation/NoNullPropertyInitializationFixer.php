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
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author ntzm
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoNullPropertyInitializationFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Properties MUST not be explicitly initialised with `null` except when they have a type declaration (PHP 7.4).',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class Foo {
                            public $bar = null;
                            public ?string $baz = null;
                            public ?string $baux;
                        }

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class Foo {
                            public static $foo = null;
                        }

                        PHP,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_CLASS, \T_TRAIT]) && $tokens->isAnyTokenKindsFound([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_VAR, \T_STATIC]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $inClass = [];
        $classLevel = 0;

        for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
            if ($tokens[$index]->isGivenKind([\T_CLASS, \T_TRAIT])) { // Enums and interfaces do not have properties
                ++$classLevel;
                $inClass[$classLevel] = 1;

                $index = $tokens->getNextTokenOfKind($index, ['{']);

                continue;
            }

            if (0 === $classLevel) {
                continue;
            }

            \assert(isset($inClass[$classLevel]));

            if ($tokens[$index]->equals('{')) {
                ++$inClass[$classLevel];

                continue;
            }

            if ($tokens[$index]->equals('}')) {
                --$inClass[$classLevel];

                if (0 === $inClass[$classLevel]) {
                    unset($inClass[$classLevel]);
                    --$classLevel;
                }

                continue;
            }

            // Ensure we are in a class but not in a method in case there are static variables defined
            if (1 !== $inClass[$classLevel]) {
                continue;
            }

            if (!$tokens[$index]->isGivenKind([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_VAR, \T_STATIC])) {
                continue;
            }

            while (true) {
                $varTokenIndex = $index = $tokens->getNextMeaningfulToken($index);

                if ($tokens[$index]->isGivenKind(\T_STATIC)) {
                    $varTokenIndex = $index = $tokens->getNextMeaningfulToken($index);
                }

                if (!$tokens[$index]->isGivenKind(\T_VARIABLE)) {
                    break;
                }

                $index = $tokens->getNextMeaningfulToken($index);

                if ($tokens[$index]->equals('=')) {
                    $index = $tokens->getNextMeaningfulToken($index);

                    if ($tokens[$index]->isGivenKind(\T_NS_SEPARATOR)) {
                        $index = $tokens->getNextMeaningfulToken($index);
                    }

                    if ($tokens[$index]->equals([\T_STRING, 'null'], false)) {
                        for ($i = $varTokenIndex + 1; $i <= $index; ++$i) {
                            if (
                                !($tokens[$i]->isWhitespace() && str_contains($tokens[$i]->getContent(), "\n"))
                                && !$tokens[$i]->isComment()
                            ) {
                                $tokens->clearAt($i);
                            }
                        }
                    }

                    ++$index;
                }

                if (!$tokens[$index]->equals(',')) {
                    break;
                }
            }
        }
    }
}
