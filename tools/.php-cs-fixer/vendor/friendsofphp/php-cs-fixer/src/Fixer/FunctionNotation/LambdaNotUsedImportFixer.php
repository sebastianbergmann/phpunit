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
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class LambdaNotUsedImportFixer extends AbstractFixer
{
    private ArgumentsAnalyzer $argumentsAnalyzer;

    private FunctionsAnalyzer $functionAnalyzer;

    private TokensAnalyzer $tokensAnalyzer;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Lambda must not import variables it doesn\'t use.',
            [new CodeSample("<?php\n\$foo = function() use (\$bar) {};\n")],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before MethodArgumentSpaceFixer, NoSpacesInsideParenthesisFixer, SpacesInsideParenthesesFixer.
     */
    public function getPriority(): int
    {
        return 31;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_FUNCTION, CT::T_USE_LAMBDA]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->argumentsAnalyzer = new ArgumentsAnalyzer();
        $this->functionAnalyzer = new FunctionsAnalyzer();
        $this->tokensAnalyzer = new TokensAnalyzer($tokens);

        for ($index = $tokens->count() - 4; $index > 0; --$index) {
            $lambdaUseIndex = $this->getLambdaUseIndex($tokens, $index);

            if (false !== $lambdaUseIndex) {
                $this->fixLambda($tokens, $lambdaUseIndex);
            }
        }
    }

    private function fixLambda(Tokens $tokens, int $lambdaUseIndex): void
    {
        $lambdaUseOpenBraceIndex = $tokens->getNextTokenOfKind($lambdaUseIndex, ['(']);
        $lambdaUseCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $lambdaUseOpenBraceIndex);
        $arguments = $this->argumentsAnalyzer->getArguments($tokens, $lambdaUseOpenBraceIndex, $lambdaUseCloseBraceIndex);

        $imports = $this->filterArguments($tokens, $arguments);

        if (0 === \count($imports)) {
            return; // no imports to remove
        }

        $notUsedImports = $this->findNotUsedLambdaImports($tokens, $imports, $lambdaUseCloseBraceIndex);
        $notUsedImportsCount = \count($notUsedImports);

        if (0 === $notUsedImportsCount) {
            return; // no not used imports found
        }

        if ($notUsedImportsCount === \count($arguments)) {
            $this->clearImportsAndUse($tokens, $lambdaUseIndex, $lambdaUseCloseBraceIndex); // all imports are not used

            return;
        }

        $this->clearImports($tokens, array_reverse($notUsedImports));
    }

    /**
     * @param array<string, int> $imports
     *
     * @return array<string, int>
     */
    private function findNotUsedLambdaImports(Tokens $tokens, array $imports, int $lambdaUseCloseBraceIndex): array
    {
        // figure out where the lambda starts ...
        $lambdaOpenIndex = $tokens->getNextTokenOfKind($lambdaUseCloseBraceIndex, ['{']);
        $curlyBracesLevel = 0;

        for ($index = $lambdaOpenIndex;; ++$index) { // go through the body of the lambda and keep count of the (possible) usages of the imported variables
            $token = $tokens[$index];

            if ($token->equals('{')) {
                ++$curlyBracesLevel;

                continue;
            }

            if ($token->equals('}')) {
                --$curlyBracesLevel;

                if (0 === $curlyBracesLevel) {
                    break;
                }

                continue;
            }

            if ($token->isGivenKind(\T_STRING) && 'compact' === strtolower($token->getContent()) && $this->functionAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                return []; // wouldn't touch it with a ten-foot pole
            }

            if ($token->isGivenKind([
                CT::T_DYNAMIC_VAR_BRACE_OPEN,
                \T_EVAL,
                \T_INCLUDE,
                \T_INCLUDE_ONCE,
                \T_REQUIRE,
                \T_REQUIRE_ONCE,
            ])) {
                return [];
            }

            if ($token->equals('$')) {
                $nextIndex = $tokens->getNextMeaningfulToken($index);

                if ($tokens[$nextIndex]->isGivenKind(\T_VARIABLE)) {
                    return []; // "$$a" case
                }
            }

            if ($token->isGivenKind(\T_VARIABLE)) {
                $content = $token->getContent();

                if (isset($imports[$content])) {
                    unset($imports[$content]);

                    if (0 === \count($imports)) {
                        return $imports;
                    }
                }
            }

            if ($token->isGivenKind(\T_STRING_VARNAME)) {
                $content = '$'.$token->getContent();

                if (isset($imports[$content])) {
                    unset($imports[$content]);

                    if (0 === \count($imports)) {
                        return $imports;
                    }
                }
            }

            if ($token->isClassy()) { // is anonymous class
                // check if used as argument in the constructor of the anonymous class
                $index = $tokens->getNextTokenOfKind($index, ['(', '{']);

                if ($tokens[$index]->equals('(')) {
                    $closeBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index);
                    $arguments = $this->argumentsAnalyzer->getArguments($tokens, $index, $closeBraceIndex);

                    $imports = $this->countImportsUsedAsArgument($tokens, $imports, $arguments);

                    $index = $tokens->getNextTokenOfKind($closeBraceIndex, ['{']);
                }

                // skip body
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $index);

                continue;
            }

            if ($token->isGivenKind(\T_FUNCTION)) {
                // check if used as argument
                $lambdaUseOpenBraceIndex = $tokens->getNextTokenOfKind($index, ['(']);
                $lambdaUseCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $lambdaUseOpenBraceIndex);
                $arguments = $this->argumentsAnalyzer->getArguments($tokens, $lambdaUseOpenBraceIndex, $lambdaUseCloseBraceIndex);

                $imports = $this->countImportsUsedAsArgument($tokens, $imports, $arguments);

                // check if used as import
                $index = $tokens->getNextTokenOfKind($index, [[CT::T_USE_LAMBDA], '{']);

                if ($tokens[$index]->isGivenKind(CT::T_USE_LAMBDA)) {
                    $lambdaUseOpenBraceIndex = $tokens->getNextTokenOfKind($index, ['(']);
                    $lambdaUseCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $lambdaUseOpenBraceIndex);
                    $arguments = $this->argumentsAnalyzer->getArguments($tokens, $lambdaUseOpenBraceIndex, $lambdaUseCloseBraceIndex);

                    $imports = $this->countImportsUsedAsArgument($tokens, $imports, $arguments);

                    $index = $tokens->getNextTokenOfKind($lambdaUseCloseBraceIndex, ['{']);
                }

                // skip body
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $index);

                continue;
            }
        }

        return $imports;
    }

    /**
     * @param array<string, int> $imports
     * @param array<int, int>    $arguments
     *
     * @return array<string, int>
     */
    private function countImportsUsedAsArgument(Tokens $tokens, array $imports, array $arguments): array
    {
        foreach ($arguments as $start => $end) {
            $info = $this->argumentsAnalyzer->getArgumentInfo($tokens, $start, $end);
            $content = $info->getName();

            if (null !== $content && isset($imports[$content])) {
                unset($imports[$content]);

                if (0 === \count($imports)) {
                    return $imports;
                }
            }
        }

        return $imports;
    }

    /**
     * @return false|int
     */
    private function getLambdaUseIndex(Tokens $tokens, int $index)
    {
        if (!$tokens[$index]->isGivenKind(\T_FUNCTION) || !$this->tokensAnalyzer->isLambda($index)) {
            return false;
        }

        $lambdaUseIndex = $tokens->getNextMeaningfulToken($index); // we are @ '(' or '&' after this

        if ($tokens[$lambdaUseIndex]->isGivenKind(CT::T_RETURN_REF)) {
            $lambdaUseIndex = $tokens->getNextMeaningfulToken($lambdaUseIndex);
        }

        $lambdaUseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $lambdaUseIndex); // we are @ ')' after this
        $lambdaUseIndex = $tokens->getNextMeaningfulToken($lambdaUseIndex);

        if (!$tokens[$lambdaUseIndex]->isGivenKind(CT::T_USE_LAMBDA)) {
            return false;
        }

        return $lambdaUseIndex;
    }

    /**
     * @param array<int, int> $arguments
     *
     * @return array<string, int>
     */
    private function filterArguments(Tokens $tokens, array $arguments): array
    {
        $imports = [];

        foreach ($arguments as $start => $end) {
            $info = $this->argumentsAnalyzer->getArgumentInfo($tokens, $start, $end);
            $argument = $info->getNameIndex();

            if ($tokens[$tokens->getPrevMeaningfulToken($argument)]->equals('&')) {
                continue;
            }

            $argumentCandidate = $tokens[$argument];

            if ('$this' === $argumentCandidate->getContent()) {
                continue;
            }

            if ($this->tokensAnalyzer->isSuperGlobal($argument)) {
                continue;
            }

            $imports[$argumentCandidate->getContent()] = $argument;
        }

        return $imports;
    }

    /**
     * @param array<string, int> $imports
     */
    private function clearImports(Tokens $tokens, array $imports): void
    {
        foreach ($imports as $removeIndex) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($removeIndex);
            $previousRemoveIndex = $tokens->getPrevMeaningfulToken($removeIndex);

            if ($tokens[$previousRemoveIndex]->equals(',')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($previousRemoveIndex);
            } elseif ($tokens[$previousRemoveIndex]->equals('(')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($tokens->getNextMeaningfulToken($removeIndex)); // next is always ',' here
            }
        }
    }

    /**
     * Remove `use` and all imported variables.
     */
    private function clearImportsAndUse(Tokens $tokens, int $lambdaUseIndex, int $lambdaUseCloseBraceIndex): void
    {
        for ($i = $lambdaUseCloseBraceIndex; $i >= $lambdaUseIndex; --$i) {
            if ($tokens[$i]->isComment()) {
                continue;
            }

            if ($tokens[$i]->isWhitespace()) {
                $previousIndex = $tokens->getPrevNonWhitespace($i);

                if ($tokens[$previousIndex]->isComment()) {
                    continue;
                }
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($i);
        }
    }
}
