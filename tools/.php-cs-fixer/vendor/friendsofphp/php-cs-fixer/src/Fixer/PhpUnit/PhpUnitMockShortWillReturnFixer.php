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
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Michał Adamski <michal.adamski@gmail.com>
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpUnitMockShortWillReturnFixer extends AbstractPhpUnitFixer
{
    private const RETURN_METHODS_MAP = [
        'returnargument' => 'willReturnArgument',
        'returncallback' => 'willReturnCallback',
        'returnself' => 'willReturnSelf',
        'returnvalue' => 'willReturn',
        'returnvaluemap' => 'willReturnMap',
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Usage of PHPUnit\'s mock e.g. `->will($this->returnValue(..))` must be replaced by its shorter equivalent such as `->willReturn(...)`.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class MyTest extends \PHPUnit_Framework_TestCase
                        {
                            public function testSomeTest()
                            {
                                $someMock = $this->createMock(Some::class);
                                $someMock->method("some")->will($this->returnSelf());
                                $someMock->method("some")->will($this->returnValue("example"));
                                $someMock->method("some")->will($this->returnArgument(2));
                                $someMock->method("some")->will($this->returnCallback("str_rot13"));
                                $someMock->method("some")->will($this->returnValueMap(["a","b","c"]));
                            }
                        }

                        PHP,
                ),
            ],
            null,
            'Risky when PHPUnit classes are overridden or not accessible, or when project has PHPUnit incompatibilities.',
        );
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();

        for ($index = $startIndex; $index < $endIndex; ++$index) {
            if (!$tokens[$index]->isObjectOperator()) {
                continue;
            }

            $functionToReplaceIndex = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$functionToReplaceIndex]->equals([\T_STRING, 'will'], false)) {
                continue;
            }

            $functionToReplaceOpeningBraceIndex = $tokens->getNextMeaningfulToken($functionToReplaceIndex);

            if (!$tokens[$functionToReplaceOpeningBraceIndex]->equals('(')) {
                continue;
            }

            $classReferenceIndex = $tokens->getNextMeaningfulToken($functionToReplaceOpeningBraceIndex);
            $objectOperatorIndex = $tokens->getNextMeaningfulToken($classReferenceIndex);
            $functionToRemoveIndex = $tokens->getNextMeaningfulToken($objectOperatorIndex);

            if (!$functionsAnalyzer->isTheSameClassCall($tokens, $functionToRemoveIndex)) {
                continue;
            }

            if (!\array_key_exists(strtolower($tokens[$functionToRemoveIndex]->getContent()), self::RETURN_METHODS_MAP)) {
                continue;
            }

            $openingBraceIndex = $tokens->getNextMeaningfulToken($functionToRemoveIndex);

            if ($tokens[$tokens->getNextMeaningfulToken($openingBraceIndex)]->isGivenKind(CT::T_FIRST_CLASS_CALLABLE)) {
                continue;
            }

            $closingBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openingBraceIndex);

            $tokens[$functionToReplaceIndex] = new Token([\T_STRING, self::RETURN_METHODS_MAP[strtolower($tokens[$functionToRemoveIndex]->getContent())]]);
            $tokens->clearTokenAndMergeSurroundingWhitespace($classReferenceIndex);
            $tokens->clearTokenAndMergeSurroundingWhitespace($objectOperatorIndex);
            $tokens->clearTokenAndMergeSurroundingWhitespace($functionToRemoveIndex);
            $tokens->clearTokenAndMergeSurroundingWhitespace($openingBraceIndex);
            $tokens->clearTokenAndMergeSurroundingWhitespace($closingBraceIndex);

            $commaAfterClosingBraceIndex = $tokens->getNextMeaningfulToken($closingBraceIndex);
            if ($tokens[$commaAfterClosingBraceIndex]->equals(',')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($commaAfterClosingBraceIndex);
            }
        }
    }
}
