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
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * Removes redundant readonly from properties in readonly classes.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoRedundantReadonlyPropertyFixer extends AbstractFixer
{
    private const PROPERTY_TYPE_DECLARATION_KINDS = [\T_STRING, \T_NS_SEPARATOR, CT::T_NULLABLE_TYPE, CT::T_ARRAY_TYPEHINT, CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION, CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN, CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE];
    private const EXPECTED_KINDS_GENERIC = [\T_ABSTRACT, \T_FINAL, \T_PRIVATE, \T_PROTECTED, \T_PUBLIC, \T_STATIC, \T_VAR, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE, FCT::T_READONLY, FCT::T_PRIVATE_SET, FCT::T_PROTECTED_SET, FCT::T_PUBLIC_SET];
    private const EXPECTED_KINDS_PROPERTY_KINDS = [...self::EXPECTED_KINDS_GENERIC, ...self::PROPERTY_TYPE_DECLARATION_KINDS];

    /**
     * {@inheritdoc}
     *
     * Must run after PhpdocReadonlyClassCommentToKeywordFixer.
     */
    public function getPriority(): int
    {
        return 3;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return \PHP_VERSION_ID >= 8_02_00 && $tokens->isAnyTokenKindsFound([\T_CLASS]);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Removes redundant readonly from properties in readonly classes.',
            [
                new VersionSpecificCodeSample(
                    <<<'PHP'
                        <?php
                        readonly class Foo
                        {
                            private readonly int $bar;
                        }

                        PHP,
                    new VersionSpecification(8_02_00),
                ),
            ],
        );
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        foreach ($tokensAnalyzer->getClassyElements() as $index => $element) {
            if (!\in_array($element['type'], ['property', 'promoted_property'], true)) {
                continue;
            }

            $modifiers = $tokensAnalyzer->getClassyModifiers($element['classIndex']);
            if (null === $modifiers['readonly']) {
                continue;
            }

            $readOnlyIndex = null;
            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            while ($tokens[$prevIndex]->isGivenKind(self::EXPECTED_KINDS_PROPERTY_KINDS) || $tokens[$prevIndex]->equals('&')) {
                if ($tokens[$prevIndex]->isGivenKind(FCT::T_READONLY)) {
                    $readOnlyIndex = $prevIndex;

                    break;
                }
                $prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            }

            if (null !== $readOnlyIndex) {
                $tokens->removeTrailingWhitespace($readOnlyIndex);
                $tokens->clearAt($readOnlyIndex);
            }
        }
    }
}
