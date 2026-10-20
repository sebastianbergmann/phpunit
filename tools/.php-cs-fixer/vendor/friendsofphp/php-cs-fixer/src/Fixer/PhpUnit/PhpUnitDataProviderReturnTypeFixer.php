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
use PhpCsFixer\Tokenizer\Analyzer\DataProviderAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpUnitDataProviderReturnTypeFixer extends AbstractPhpUnitFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The return type of PHPUnit data provider must be `iterable`.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class FooTest extends TestCase {
                            /**
                             * @dataProvider provideSomethingCases
                             */
                            public function testSomething($expected, $actual) {}
                            public function provideSomethingCases(): array {}
                        }

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class FooTest extends TestCase {
                            /**
                             * @dataProvider provideSomethingCases
                             */
                            public function testSomething($expected, $actual) {}
                            public function provideSomethingCases() {}
                        }

                        PHP,
                ),
            ],
            'Data provider must return `iterable`, either an array of arrays or an object that implements the `Traversable` interface.',
            'Risky when relying on signature of the data provider.',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before ReturnToYieldFromFixer, ReturnTypeDeclarationFixer.
     * Must run after CleanNamespaceFixer.
     */
    public function getPriority(): int
    {
        return 9;
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        $dataProviderAnalyzer = new DataProviderAnalyzer();
        $functionsAnalyzer = new FunctionsAnalyzer();

        foreach (array_reverse($dataProviderAnalyzer->getDataProviders($tokens, $startIndex, $endIndex)) as $dataProviderAnalysis) {
            $typeAnalysis = $functionsAnalyzer->getFunctionReturnType($tokens, $dataProviderAnalysis->getNameIndex());

            if (null === $typeAnalysis) {
                $argumentsStart = $tokens->getNextTokenOfKind($dataProviderAnalysis->getNameIndex(), ['(']);
                $argumentsEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $argumentsStart);

                $tokens->insertAt(
                    $argumentsEnd + 1,
                    [
                        new Token([CT::T_TYPE_COLON, ':']),
                        new Token([\T_WHITESPACE, ' ']),
                        new Token([\T_STRING, 'iterable']),
                    ],
                );

                continue;
            }

            if ('iterable' === $typeAnalysis->getName()) {
                continue;
            }

            $typeStartIndex = $tokens->getNextMeaningfulToken($typeAnalysis->getStartIndex() - 1);
            $typeEndIndex = $typeAnalysis->getEndIndex();

            // @TODO: drop condition and it's body when PHP 8+ is required
            if ($tokens->generatePartialCode($typeStartIndex, $typeEndIndex) !== $typeAnalysis->getName()) {
                continue;
            }

            $tokens->overrideRange($typeStartIndex, $typeEndIndex, [new Token([\T_STRING, 'iterable'])]);
        }
    }
}
