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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_the_sleep_and_wakeup_magic_methods
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ModernSerializationMethodsFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Use new serialization methods `__serialize` and `__unserialize` instead of deprecated ones `__sleep` and `__wakeup`.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php class Foo {
                            public function __sleep() {}
                            public function __wakeup() {}
                        }

                        PHP,
                ),
            ],
            null,
            'Risky when calling the old methods directly or having logic in the `__sleep` and `__wakeup` methods.',
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_CLASS, \T_FUNCTION]);
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $methodsByClass = [];
        foreach ((new TokensAnalyzer($tokens))->getClassyElements() as $index => $element) {
            if ('method' !== $element['type']) {
                continue;
            }

            if (!isset($methodsByClass[$element['classIndex']])) {
                $methodsByClass[$element['classIndex']] = [];
            }

            $functionNameIndex = $tokens->getNextMeaningfulToken($index);
            $functionName = $tokens[$functionNameIndex]->getContent();

            $methodsByClass[$element['classIndex']][$functionName] = $functionNameIndex;
        }

        $tokensToInsert = [];
        foreach ($methodsByClass as $methods) {
            if (isset($methods['__sleep']) && !isset($methods['__serialize'])) {
                $tokens[$methods['__sleep']] = new Token([\T_STRING, '__serialize']);
            }

            if (isset($methods['__wakeup']) && !isset($methods['__unserialize'])) {
                $tokens[$methods['__wakeup']] = new Token([\T_STRING, '__unserialize']);

                $openParenthesisIndex = $tokens->getNextTokenOfKind($methods['__wakeup'], ['(']);

                $tokensToInsert[$openParenthesisIndex + 1] = [
                    new Token([CT::T_ARRAY_TYPEHINT, 'array']),
                    new Token([\T_WHITESPACE, ' ']),
                    new Token([\T_VARIABLE, '$data']),
                ];
            }
        }
        if ([] !== $tokensToInsert) {
            $tokens->insertSlices($tokensToInsert);
        }
    }
}
