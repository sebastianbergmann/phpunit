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

namespace PhpCsFixer\Fixer\PhpUnit;

use PhpCsFixer\Fixer\AbstractPhpUnitFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @author Gert de Pagter
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpUnitSetUpTearDownVisibilityFixer extends AbstractPhpUnitFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Changes the visibility of the `setUp()` and `tearDown()` functions of PHPUnit to `protected`, to match the PHPUnit TestCase.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class MyTest extends \PHPUnit_Framework_TestCase
                        {
                            private $hello;
                            public function setUp()
                            {
                                $this->hello = "hello";
                            }

                            public function tearDown()
                            {
                                $this->hello = null;
                            }
                        }

                        PHP,
                ),
            ],
            null,
            'This fixer may change functions named `setUp()` or `tearDown()` outside of PHPUnit tests, '
            .'when a class is wrongly seen as a PHPUnit test.',
        );
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        $counter = 0;
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        $slicesToInsert = [];

        for ($index = $startIndex + 1; $index < $endIndex; ++$index) {
            if (2 === $counter) {
                break; // we've seen both methods we are interested in, so stop analyzing this class
            }

            if ($tokens[$index]->equals('{')) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $index);

                continue;
            }

            if (!$tokens[$index]->isGivenKind(\T_FUNCTION)) {
                continue;
            }

            $functionNameIndex = $tokens->getNextMeaningfulToken($index);
            $functionName = strtolower($tokens[$functionNameIndex]->getContent());

            if ('setup' !== $functionName && 'teardown' !== $functionName) {
                continue;
            }

            ++$counter;

            $visibility = $tokensAnalyzer->getMethodAttributes($index)['visibility'];

            if (\T_PUBLIC === $visibility) {
                $visibilityIndex = $tokens->getPrevTokenOfKind($index, [[\T_PUBLIC]]);
                $tokens[$visibilityIndex] = new Token([\T_PROTECTED, 'protected']);

                continue;
            }

            if (null === $visibility) {
                $slicesToInsert[$index] = [new Token([\T_PROTECTED, 'protected']), new Token([\T_WHITESPACE, ' '])];
            }
        }

        $tokens->insertSlices($slicesToInsert);
    }
}
