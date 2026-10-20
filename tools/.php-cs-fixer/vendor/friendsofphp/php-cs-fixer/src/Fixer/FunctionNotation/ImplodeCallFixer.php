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

namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ImplodeCallFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Function `implode` must be called with 2 arguments in the documented order.',
            [
                new CodeSample("<?php\nimplode(\$pieces, '');\n"),
                new CodeSample("<?php\nimplode(\$pieces);\n"),
            ],
            null,
            'Risky when the function `implode` is overridden.',
        );
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_STRING);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before MethodArgumentSpaceFixer.
     * Must run after NoAliasFunctionsFixer.
     */
    public function getPriority(): int
    {
        return 37;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();

        for ($index = \count($tokens) - 1; $index > 0; --$index) {
            if (!$tokens[$index]->equals([\T_STRING, 'implode'], false)) {
                continue;
            }

            if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                continue;
            }

            $argumentsIndices = $this->getArgumentIndices($tokens, $index);

            if (1 === \count($argumentsIndices)) {
                $firstArgumentIndex = array_key_first($argumentsIndices);
                $tokens->insertAt($firstArgumentIndex, [
                    new Token([\T_CONSTANT_ENCAPSED_STRING, "''"]),
                    new Token(','),
                    new Token([\T_WHITESPACE, ' ']),
                ]);

                continue;
            }

            if (2 === \count($argumentsIndices)) {
                \assert(isset(array_keys($argumentsIndices)[0], array_keys($argumentsIndices)[1]));
                [$firstArgumentIndex, $secondArgumentIndex] = array_keys($argumentsIndices);

                // If the first argument is string we have nothing to do
                if ($tokens[$firstArgumentIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
                    continue;
                }
                // If the second argument is not string we cannot make a swap
                if (!$tokens[$secondArgumentIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
                    continue;
                }

                // collect tokens from first argument
                \assert(isset($argumentsIndices[key($argumentsIndices)]));
                $firstArgumentEndIndex = $argumentsIndices[key($argumentsIndices)];
                $newSecondArgumentTokens = [];
                for ($i = array_key_first($argumentsIndices); $i <= $firstArgumentEndIndex; ++$i) {
                    $newSecondArgumentTokens[] = clone $tokens[$i];
                    $tokens->clearAt($i);
                }

                $tokens->insertAt($firstArgumentIndex, clone $tokens[$secondArgumentIndex]);

                // insert above increased the second argument index
                ++$secondArgumentIndex;
                $tokens->clearAt($secondArgumentIndex);
                $tokens->insertAt($secondArgumentIndex, $newSecondArgumentTokens);
            }
        }
    }

    /**
     * @return array<int, int> In the format: startIndex => endIndex
     */
    private function getArgumentIndices(Tokens $tokens, int $functionNameIndex): array
    {
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        $openParenthesis = $tokens->getNextTokenOfKind($functionNameIndex, ['(']);
        $closeParenthesis = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesis);

        $indices = [];

        foreach ($argumentsAnalyzer->getArguments($tokens, $openParenthesis, $closeParenthesis) as $startIndexCandidate => $endIndex) {
            $indices[$tokens->getNextMeaningfulToken($startIndexCandidate - 1)] = $tokens->getPrevMeaningfulToken($endIndex + 1);
        }

        return $indices;
    }
}
