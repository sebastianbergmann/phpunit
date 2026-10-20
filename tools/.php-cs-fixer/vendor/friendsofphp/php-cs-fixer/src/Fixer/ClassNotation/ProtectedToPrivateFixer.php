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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ProtectedToPrivateFixer extends AbstractFixer
{
    private const MODIFIER_KINDS = [\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_FINAL, \T_ABSTRACT, \T_NS_SEPARATOR, \T_STRING, CT::T_NULLABLE_TYPE, CT::T_ARRAY_TYPEHINT, \T_STATIC, CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION, FCT::T_READONLY, FCT::T_PRIVATE_SET, FCT::T_PROTECTED_SET];
    private TokensAnalyzer $tokensAnalyzer;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Converts `protected` variables and methods to `private` where possible.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class Sample
                        {
                            protected $a;

                            protected function test()
                            {
                            }
                        }

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before OrderedClassElementsFixer, StaticPrivateMethodFixer.
     * Must run after FinalClassFixer, FinalInternalClassFixer.
     */
    public function getPriority(): int
    {
        return 66;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_PROTECTED, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, FCT::T_PROTECTED_SET])
            && (
                $tokens->isAllTokenKindsFound([\T_CLASS, \T_FINAL])
                || $tokens->isTokenKindFound(FCT::T_ENUM)
            );
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->tokensAnalyzer = new TokensAnalyzer($tokens);

        $classesCandidate = [];
        $classElementTypes = ['method' => true, 'property' => true, 'promoted_property' => true, 'const' => true];

        foreach ($this->tokensAnalyzer->getClassyElements() as $index => $element) {
            $classIndex = $element['classIndex'];

            $classesCandidate[$classIndex] ??= $this->isClassCandidate($tokens, $classIndex);

            if (false === $classesCandidate[$classIndex]) {
                continue;
            }

            if (!isset($classElementTypes[$element['type']])) {
                continue;
            }

            $previousIndex = $index;
            $protectedIndex = null;
            $protectedPromotedIndex = null;
            $protectedSetIndex = null;
            $isFinal = false;

            do {
                $previousIndex = $tokens->getPrevMeaningfulToken($previousIndex);

                if ($tokens[$previousIndex]->isGivenKind(\T_PROTECTED)) {
                    $protectedIndex = $previousIndex;
                } elseif ($tokens[$previousIndex]->isGivenKind(CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED)) {
                    $protectedPromotedIndex = $previousIndex;
                } elseif ($tokens[$previousIndex]->isGivenKind(FCT::T_PROTECTED_SET)) {
                    $protectedSetIndex = $previousIndex;
                } elseif ($tokens[$previousIndex]->isGivenKind(\T_FINAL)) {
                    $isFinal = true;
                }
            } while ($tokens[$previousIndex]->isGivenKind(self::MODIFIER_KINDS));

            if ($isFinal && 'const' === $element['type']) {
                continue; // Final constants cannot be private
            }

            if (null !== $protectedIndex) {
                $tokens[$protectedIndex] = new Token([\T_PRIVATE, 'private']);
            }
            if (null !== $protectedPromotedIndex) {
                $tokens[$protectedPromotedIndex] = new Token([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE, 'private']);
            }
            if (null !== $protectedSetIndex) {
                $tokens[$protectedSetIndex] = new Token([\T_PRIVATE_SET, 'private(set)']);
            }
        }
    }

    /**
     * Consider symbol as candidate for fixing if it's:
     *   - an Enum (PHP8.1+)
     *   - a class, which:
     *     - is not anonymous
     *     - is final
     *     - does not use traits
     *     - does not extend other class.
     */
    private function isClassCandidate(Tokens $tokens, int $classIndex): bool
    {
        if ($tokens[$classIndex]->isGivenKind(FCT::T_ENUM)) {
            return true;
        }

        if (!$tokens[$classIndex]->isGivenKind(\T_CLASS) || $this->tokensAnalyzer->isAnonymousClass($classIndex)) {
            return false;
        }

        $modifiers = $this->tokensAnalyzer->getClassyModifiers($classIndex);

        if (!isset($modifiers['final'])) {
            return false;
        }

        $classNameIndex = $tokens->getNextMeaningfulToken($classIndex); // move to class name as anonymous class is never "final"
        $classExtendsIndex = $tokens->getNextMeaningfulToken($classNameIndex); // move to possible "extends"

        if ($tokens[$classExtendsIndex]->isGivenKind(\T_EXTENDS)) {
            return false;
        }

        if (!$tokens->isTokenKindFound(CT::T_USE_TRAIT)) {
            return true; // cheap test
        }

        $classOpenIndex = $tokens->getNextTokenOfKind($classNameIndex, ['{']);
        $classCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $classOpenIndex);
        $useIndex = $tokens->getNextTokenOfKind($classOpenIndex, [[CT::T_USE_TRAIT]]);

        return null === $useIndex || $useIndex > $classCloseIndex;
    }
}
