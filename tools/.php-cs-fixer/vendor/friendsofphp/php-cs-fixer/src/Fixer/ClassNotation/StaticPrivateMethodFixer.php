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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class StaticPrivateMethodFixer extends AbstractFixer
{
    /**
     * @var array<string, true>
     */
    private const MAGIC_METHODS = [
        '__clone' => true,
        '__construct' => true,
        '__destruct' => true,
        '__wakeup' => true,
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Converts private methods to `static` where possible.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class Foo
                        {
                            public function bar()
                            {
                                return $this->baz();
                            }

                            private function baz()
                            {
                                return 1;
                            }
                        }

                        PHP,
                ),
            ],
            null,
            'Risky when the method:'
            .' contains dynamic generated calls to the instance,'
            .' is dynamically referenced,'
            .' is referenced inside a Trait the class uses.',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before StaticLambdaFixer.
     * Must run after ProtectedToPrivateFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_PRIVATE, \T_FUNCTION]);
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        do {
            $anythingChanged = false;

            $end = \count($tokens) - 3; // min. number of tokens to form a class candidate to fix
            for ($index = $end; $index > 0; --$index) {
                if (!$tokens[$index]->isGivenKind(\T_CLASS)) {
                    continue;
                }

                $classOpen = $tokens->getNextTokenOfKind($index, ['{']);
                $classClose = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $classOpen);

                $anythingChanged = $this->fixClass($tokens, $tokensAnalyzer, $classOpen, $classClose) || $anythingChanged;
            }
        } while ($anythingChanged);
    }

    private function fixClass(Tokens $tokens, TokensAnalyzer $tokensAnalyzer, int $classOpen, int $classClose): bool
    {
        $fixedMethods = [];
        foreach ($this->getClassMethods($tokens, $classOpen, $classClose) as $methodData) {
            [$functionKeywordIndex, $methodOpen, $methodClose] = $methodData;

            if ($this->skipMethod($tokens, $tokensAnalyzer, $functionKeywordIndex, $methodOpen, $methodClose)) {
                continue;
            }

            $methodNameIndex = $tokens->getNextMeaningfulToken($functionKeywordIndex);
            $methodName = $tokens[$methodNameIndex]->getContent();
            $fixedMethods[$methodName] = true;

            $tokens->insertSlices([$functionKeywordIndex => [new Token([\T_STATIC, 'static']), new Token([\T_WHITESPACE, ' '])]]);
        }

        if (0 === \count($fixedMethods)) {
            return false;
        }

        $classClose = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $classOpen);
        foreach ($this->getClassMethods($tokens, $classOpen, $classClose) as $methodData) {
            [, $methodOpen, $methodClose] = $methodData;

            $this->fixReferencesInFunction($tokens, $tokensAnalyzer, $methodOpen, $methodClose, $fixedMethods);
        }

        return true;
    }

    private function skipMethod(Tokens $tokens, TokensAnalyzer $tokensAnalyzer, int $functionKeywordIndex, int $methodOpen, int $methodClose): bool
    {
        $methodNameIndex = $tokens->getNextMeaningfulToken($functionKeywordIndex);
        $methodName = strtolower($tokens[$methodNameIndex]->getContent());
        if (isset(self::MAGIC_METHODS[$methodName])) {
            return true;
        }

        $prevTokenIndex = $tokens->getPrevMeaningfulToken($functionKeywordIndex);
        if ($tokens[$prevTokenIndex]->isGivenKind(\T_FINAL)) {
            $prevTokenIndex = $tokens->getPrevMeaningfulToken($prevTokenIndex);
        }
        if (!$tokens[$prevTokenIndex]->isGivenKind(\T_PRIVATE)) {
            return true;
        }

        $prePrevTokenIndex = $tokens->getPrevMeaningfulToken($prevTokenIndex);
        if ($tokens[$prePrevTokenIndex]->isGivenKind(\T_STATIC)) {
            return true;
        }

        for ($index = $methodOpen + 1; $index < $methodClose - 1; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_CLASS) && $tokensAnalyzer->isAnonymousClass($index)) {
                $anonymousClassOpen = $tokens->getNextTokenOfKind($index, ['{']);
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $anonymousClassOpen);

                continue;
            }

            if ($tokens[$index]->isGivenKind(\T_FUNCTION)) {
                return true;
            }

            if ($tokens[$index]->equals([\T_VARIABLE, '$this'])) {
                $operatorIndex = $tokens->getNextMeaningfulToken($index);
                $methodNameIndex = $tokens->getNextMeaningfulToken($operatorIndex);
                $argumentsBraceIndex = $tokens->getNextMeaningfulToken($methodNameIndex);

                if (
                    !$tokens[$operatorIndex]->isGivenKind(\T_OBJECT_OPERATOR)
                    || $methodName !== $tokens[$methodNameIndex]->getContent()
                    || !$tokens[$argumentsBraceIndex]->equals('(')
                ) {
                    return true;
                }
            }

            if ($tokens[$index]->equals([\T_STRING, 'debug_backtrace'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, bool> $fixedMethods
     */
    private function fixReferencesInFunction(Tokens $tokens, TokensAnalyzer $tokensAnalyzer, int $methodOpen, int $methodClose, array $fixedMethods): void
    {
        for ($index = $methodOpen + 1; $index < $methodClose - 1; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_FUNCTION)) {
                $prevIndex = $tokens->getPrevMeaningfulToken($index);
                $closureStart = $tokens->getNextTokenOfKind($index, ['{']);
                $closureEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $closureStart);
                if (!$tokens[$prevIndex]->isGivenKind(\T_STATIC)) {
                    $this->fixReferencesInFunction($tokens, $tokensAnalyzer, $closureStart, $closureEnd, $fixedMethods);
                }

                $index = $closureEnd;

                continue;
            }

            if ($tokens[$index]->isGivenKind(\T_CLASS) && $tokensAnalyzer->isAnonymousClass($index)) {
                $anonymousClassOpen = $tokens->getNextTokenOfKind($index, ['{']);
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $anonymousClassOpen);

                continue;
            }

            if (!$tokens[$index]->equals([\T_VARIABLE, '$this'])) {
                continue;
            }

            $objectOperatorIndex = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$objectOperatorIndex]->isGivenKind(\T_OBJECT_OPERATOR)) {
                continue;
            }

            $methodNameIndex = $tokens->getNextMeaningfulToken($objectOperatorIndex);
            $argumentsBraceIndex = $tokens->getNextMeaningfulToken($methodNameIndex);
            if (!$tokens[$argumentsBraceIndex]->equals('(')) {
                continue;
            }

            $currentMethodName = $tokens[$methodNameIndex]->getContent();
            if (!isset($fixedMethods[$currentMethodName])) {
                continue;
            }

            $tokens[$index] = new Token([\T_STRING, 'self']);
            $tokens[$objectOperatorIndex] = new Token([\T_DOUBLE_COLON, '::']);
        }
    }

    /**
     * @return iterable<array{int, int, int}>
     */
    private function getClassMethods(Tokens $tokens, int $classOpen, int $classClose): iterable
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        for ($index = $classClose - 1; $index > $classOpen + 1; --$index) {
            if ($tokens[$index]->equals('}')) {
                $index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_BRACE, $index);

                continue;
            }

            if (!$tokens[$index]->isGivenKind(\T_FUNCTION)) {
                continue;
            }

            if ($tokensAnalyzer->getMethodAttributes($index)['abstract']) {
                continue;
            }

            $methodOpen = $tokens->getNextTokenOfKind($index, ['{']);
            $methodClose = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $methodOpen);

            yield [$index, $methodOpen, $methodClose];
        }
    }
}
