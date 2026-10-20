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
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class RegularCallableCallFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Callables must be called without using `call_user_func*` when possible.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                            call_user_func("var_dump", 1, 2);

                            call_user_func("Bar\Baz::d", 1, 2);

                            call_user_func_array($callback, [1, 2]);

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        call_user_func(function ($a, $b) { var_dump($a, $b); }, 1, 2);

                        call_user_func(static function ($a, $b) { var_dump($a, $b); }, 1, 2);

                        PHP,
                ),
            ],
            null,
            'Risky when the `call_user_func` or `call_user_func_array` function is overridden or when are used in constructions that should be avoided, like `call_user_func_array(\'foo\', [\'bar\' => \'baz\'])` or `call_user_func($foo, $foo = \'bar\')`.',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before NativeFunctionInvocationFixer.
     * Must run after NoBinaryStringFixer, NoUselessConcatOperatorFixer.
     */
    public function getPriority(): int
    {
        return 2;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_STRING);
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();
        $argumentsAnalyzer = new ArgumentsAnalyzer();

        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (!$tokens[$index]->equalsAny([[\T_STRING, 'call_user_func'], [\T_STRING, 'call_user_func_array']], false)) {
                continue;
            }

            if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                continue; // redeclare/override
            }

            $openParenthesis = $tokens->getNextMeaningfulToken($index);
            $closeParenthesis = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesis);
            $arguments = $argumentsAnalyzer->getArguments($tokens, $openParenthesis, $closeParenthesis);

            if (1 > \count($arguments)) {
                return; // no arguments!
            }

            $this->processCall($tokens, $index, $arguments);
        }
    }

    /**
     * @param non-empty-array<int, int> $arguments
     */
    private function processCall(Tokens $tokens, int $index, array $arguments): void
    {
        $firstArgIndex = $tokens->getNextMeaningfulToken(
            $tokens->getNextMeaningfulToken($index),
        );

        $firstArgToken = $tokens[$firstArgIndex];

        if ($firstArgToken->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
            $afterFirstArgIndex = $tokens->getNextMeaningfulToken($firstArgIndex);

            if (!$tokens[$afterFirstArgIndex]->equalsAny([',', ')'])) {
                return; // first argument is an expression like `call_user_func("foo"."bar", ...)`, not supported!
            }

            $firstArgTokenContent = $firstArgToken->getContent();

            if (!$this->isValidFunctionInvoke($firstArgTokenContent)) {
                return;
            }

            $newCallTokens = Tokens::fromCode('<?php '.substr(str_replace('\\\\', '\\', $firstArgToken->getContent()), 1, -1).'();');
            $newCallTokensSize = $newCallTokens->count();
            $newCallTokens->clearAt(0);
            $newCallTokens->clearRange($newCallTokensSize - 3, $newCallTokensSize - 1);
            $newCallTokens->clearEmptyTokens();

            $this->replaceCallUserFuncWithCallback($tokens, $index, $newCallTokens, $firstArgIndex, $firstArgIndex);
        } elseif (
            $firstArgToken->isGivenKind(\T_FUNCTION)
            || (
                $firstArgToken->isGivenKind(\T_STATIC)
                && $tokens[$tokens->getNextMeaningfulToken($firstArgIndex)]->isGivenKind(\T_FUNCTION)
            )
        ) {
            $firstArgEndIndex = $tokens->findBlockEnd(
                Tokens::BLOCK_TYPE_BRACE,
                $tokens->getNextTokenOfKind($firstArgIndex, ['{']),
            );

            $newCallTokens = $this->getTokensSubcollection($tokens, $firstArgIndex, $firstArgEndIndex);
            $newCallTokens->insertAt($newCallTokens->count(), new Token(')'));
            $newCallTokens->insertAt(0, new Token('('));
            $this->replaceCallUserFuncWithCallback($tokens, $index, $newCallTokens, $firstArgIndex, $firstArgEndIndex);
        } elseif ($firstArgToken->isGivenKind(\T_VARIABLE)) {
            $firstArgEndIndex = reset($arguments);

            // check if the same variable is used multiple times and if so do not fix

            foreach ($arguments as $argumentStart => $argumentEnd) {
                if ($firstArgEndIndex === $argumentEnd) {
                    continue;
                }

                for ($i = $argumentStart; $i <= $argumentEnd; ++$i) {
                    if ($tokens[$i]->equals($firstArgToken)) {
                        return;
                    }
                }
            }

            // check if complex statement and if so wrap the call in () if on PHP 7 or up, else do not fix

            $newCallTokens = $this->getTokensSubcollection($tokens, $firstArgIndex, $firstArgEndIndex);
            $complex = false;

            for ($newCallIndex = \count($newCallTokens) - 1; $newCallIndex >= 0; --$newCallIndex) {
                if ($newCallTokens[$newCallIndex]->isGivenKind([\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT, \T_VARIABLE])) {
                    continue;
                }

                $blockType = Tokens::detectBlockType($newCallTokens[$newCallIndex]);

                if (null !== $blockType && (Tokens::BLOCK_TYPE_INDEX_BRACE === $blockType['type'] || Tokens::BLOCK_TYPE_INDEX_BRACKET === $blockType['type'])) {
                    $newCallIndex = $newCallTokens->findBlockStart($blockType['type'], $newCallIndex);

                    continue;
                }

                $complex = true;

                break;
            }

            if ($complex) {
                $newCallTokens->insertAt($newCallTokens->count(), new Token(')'));
                $newCallTokens->insertAt(0, new Token('('));
            }
            $this->replaceCallUserFuncWithCallback($tokens, $index, $newCallTokens, $firstArgIndex, $firstArgEndIndex);
        }
    }

    private function replaceCallUserFuncWithCallback(Tokens $tokens, int $callIndex, Tokens $newCallTokens, int $firstArgStartIndex, int $firstArgEndIndex): void
    {
        $tokens->clearRange($firstArgStartIndex, $firstArgEndIndex);

        $afterFirstArgIndex = $tokens->getNextMeaningfulToken($firstArgEndIndex);
        $afterFirstArgToken = $tokens[$afterFirstArgIndex];

        if ($afterFirstArgToken->equals(',')) {
            $useEllipsis = $tokens[$callIndex]->equals([\T_STRING, 'call_user_func_array'], false);

            if ($useEllipsis) {
                $secondArgIndex = $tokens->getNextMeaningfulToken($afterFirstArgIndex);
                $tokens->insertAt($secondArgIndex, new Token([\T_ELLIPSIS, '...']));
            }

            $tokens->clearAt($afterFirstArgIndex);
            $tokens->removeTrailingWhitespace($afterFirstArgIndex);
        }

        $tokens->overrideRange($callIndex, $callIndex, $newCallTokens);
        $prevIndex = $tokens->getPrevMeaningfulToken($callIndex);

        if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($prevIndex);
        }
    }

    private function getTokensSubcollection(Tokens $tokens, int $indexStart, int $indexEnd): Tokens
    {
        $size = $indexEnd - $indexStart + 1;
        $subCollection = new Tokens($size);

        for ($i = 0; $i < $size; ++$i) {
            $toClone = $tokens[$i + $indexStart];
            $subCollection[$i] = clone $toClone;
        }

        return $subCollection;
    }

    private function isValidFunctionInvoke(string $name): bool
    {
        if (\strlen($name) < 3 || 'b' === $name[0] || 'B' === $name[0]) {
            return false;
        }

        $name = substr($name, 1, -1);

        if ($name !== trim($name)) {
            return false;
        }

        return true;
    }
}
