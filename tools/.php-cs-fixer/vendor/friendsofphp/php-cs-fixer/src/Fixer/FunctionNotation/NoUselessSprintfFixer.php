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
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoUselessSprintfFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no `sprintf` calls with only the first argument.',
            [
                new CodeSample(
                    "<?php\n\$foo = sprintf('bar');\n",
                ),
            ],
            null,
            'Risky when if the `sprintf` function is overridden.',
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
     * Must run before MethodArgumentSpaceFixer, NativeFunctionCasingFixer, NoEmptyStatementFixer, NoExtraBlankLinesFixer, NoSpacesInsideParenthesisFixer, SpacesInsideParenthesesFixer.
     */
    public function getPriority(): int
    {
        return 42;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $functionAnalyzer = new FunctionsAnalyzer();
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        for ($index = \count($tokens) - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_STRING)) {
                continue;
            }

            if ('sprintf' !== strtolower($tokens[$index]->getContent())) {
                continue;
            }

            if (!$functionAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                continue;
            }

            $openParenthesisIndex = $tokens->getNextTokenOfKind($index, ['(']);

            if ($tokens[$tokens->getNextMeaningfulToken($openParenthesisIndex)]->isGivenKind(\T_ELLIPSIS)) {
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
        }
    }
}
