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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoUselessPrintfFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no `printf` calls with only the first argument.',
            [
                new CodeSample(
                    "<?php\n\nprintf('bar');\n",
                ),
            ],
            null,
            'Risky when the `printf` function is overridden.',
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_STRING);
    }

    public function isRisky(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Must run before EchoTagSyntaxFixer, NoExtraBlankLinesFixer, NoMixedEchoPrintFixer.
     */
    public function getPriority(): int
    {
        return 10;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        $printfIndices = [];

        for ($index = \count($tokens) - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_STRING)) {
                continue;
            }

            if ('printf' !== strtolower($tokens[$index]->getContent())) {
                continue;
            }

            if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                continue;
            }

            $openParenthesisIndex = $tokens->getNextTokenOfKind($index, ['(']);

            if ($tokens[$tokens->getNextMeaningfulToken($openParenthesisIndex)]->isGivenKind([\T_ELLIPSIS, CT::T_FIRST_CLASS_CALLABLE])) {
                continue;
            }

            $closeParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesisIndex);

            if (1 !== $argumentsAnalyzer->countArguments($tokens, $openParenthesisIndex, $closeParenthesisIndex)) {
                continue;
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($closeParenthesisIndex);

            $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($closeParenthesisIndex);

            if ($tokens[$prevMeaningfulTokenIndex]->equals(',')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($prevMeaningfulTokenIndex);
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($openParenthesisIndex);
            $tokens->clearTokenAndMergeSurroundingWhitespace($index);

            $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($index);

            if ($tokens[$prevMeaningfulTokenIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($prevMeaningfulTokenIndex);
            }

            $printfIndices[] = $index;
        }

        if ([] === $printfIndices) {
            return;
        }

        $tokens->insertSlices(array_combine(
            $printfIndices,
            array_fill(0, \count($printfIndices), [new Token([\T_PRINT, 'print']), new Token([\T_WHITESPACE, ' '])]),
        ));
    }
}
