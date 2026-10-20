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
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @author Gregor Harlan <gharlan@web.de>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SelfAccessorFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Inside class or interface element `self` should be preferred to the class name itself.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class Sample
                        {
                            const BAZ = 1;
                            const BAR = Sample::BAZ;

                            public function getBar()
                            {
                                return Sample::BAR;
                            }
                        }

                        PHP,
                ),
            ],
            null,
            'Risky when using dynamic calls like get_called_class() or late static binding.',
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_CLASS, \T_INTERFACE]);
    }

    /**
     * {@inheritdoc}
     *
     * Must run after PsrAutoloadingFixer.
     */
    public function getPriority(): int
    {
        return -11;
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        foreach ($tokens->getNamespaceDeclarations() as $namespace) {
            for ($index = $namespace->getScopeStartIndex(); $index < $namespace->getScopeEndIndex(); ++$index) {
                if (!$tokens[$index]->isGivenKind([\T_CLASS, \T_INTERFACE]) || $tokensAnalyzer->isAnonymousClass($index)) {
                    continue;
                }

                $nameIndex = $tokens->getNextTokenOfKind($index, [[\T_STRING]]);
                $startIndex = $tokens->getNextTokenOfKind($nameIndex, ['{']);
                $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $startIndex);

                $name = $tokens[$nameIndex]->getContent();

                $this->replaceNameOccurrences($tokens, $namespace->getFullName(), $name, $startIndex, $endIndex);

                $index = $endIndex;
            }
        }
    }

    /**
     * Replace occurrences of the name of the classy element by "self" (if possible).
     */
    private function replaceNameOccurrences(Tokens $tokens, string $namespace, string $name, int $startIndex, int $endIndex): void
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);
        $insideMethodSignatureUntil = null;

        for ($i = $startIndex; $i < $endIndex; ++$i) {
            if ($i === $insideMethodSignatureUntil) {
                $insideMethodSignatureUntil = null;
            }

            $token = $tokens[$i];

            // skip anonymous classes
            if ($token->isGivenKind(\T_CLASS) && $tokensAnalyzer->isAnonymousClass($i)) {
                $i = $tokens->getNextTokenOfKind($i, ['{']);
                $i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $i);

                continue;
            }

            if ($token->isGivenKind(\T_FN)) {
                $i = $tokensAnalyzer->getLastTokenIndexOfArrowFunction($i);
                $i = $tokens->getNextMeaningfulToken($i);

                continue;
            }

            if ($token->isGivenKind(\T_FUNCTION)) {
                if ($tokensAnalyzer->isLambda($i)) {
                    $i = $tokens->getNextTokenOfKind($i, ['{']);
                    $i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $i);

                    continue;
                }

                $i = $tokens->getNextTokenOfKind($i, ['(']);
                $insideMethodSignatureUntil = $tokens->getNextTokenOfKind($i, ['{', ';']);

                continue;
            }

            if (!$token->equals([\T_STRING, $name], false)) {
                continue;
            }

            $nextToken = $tokens[$tokens->getNextMeaningfulToken($i)];
            if ($nextToken->isGivenKind(\T_NS_SEPARATOR)) {
                continue;
            }

            $classStartIndex = $i;
            $prevToken = $tokens[$tokens->getPrevMeaningfulToken($i)];
            if ($prevToken->isGivenKind(\T_NS_SEPARATOR)) {
                $classStartIndex = $this->getClassStart($tokens, $i, $namespace);
                if (null === $classStartIndex) {
                    continue;
                }
                $prevToken = $tokens[$tokens->getPrevMeaningfulToken($classStartIndex)];
            }
            if ($prevToken->isGivenKind([\T_STRING, \T_PAAMAYIM_NEKUDOTAYIM]) || $prevToken->isObjectOperator()) {
                continue;
            }

            if (
                $prevToken->isGivenKind([\T_INSTANCEOF, \T_NEW])
                || $nextToken->isGivenKind(\T_PAAMAYIM_NEKUDOTAYIM)
                || (
                    null !== $insideMethodSignatureUntil
                    && $i < $insideMethodSignatureUntil
                    && $prevToken->equalsAny(['(', ',', [CT::T_NULLABLE_TYPE], [CT::T_TYPE_ALTERNATION], [CT::T_TYPE_COLON]])
                )
            ) {
                for ($j = $classStartIndex; $j < $i; ++$j) {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($j);
                }
                $tokens[$i] = new Token([\T_STRING, 'self']);
            }
        }
    }

    private function getClassStart(Tokens $tokens, int $index, string $namespace): ?int
    {
        $namespace = ('' !== $namespace ? '\\'.$namespace : '').'\\';

        foreach (array_reverse(Preg::split('/(\\\)/', $namespace, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE)) as $piece) {
            $index = $tokens->getPrevMeaningfulToken($index);
            if ('\\' === $piece) {
                if (!$tokens[$index]->isGivenKind(\T_NS_SEPARATOR)) {
                    return null;
                }
            } elseif (!$tokens[$index]->equals([\T_STRING, $piece], false)) {
                return null;
            }
        }

        return $index;
    }
}
