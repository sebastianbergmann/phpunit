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

namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Utils;

/**
 * @author Ceeram <ceeram@cakephp.org>
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpdocIndentFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Docblocks should have the same indentation as the documented subject.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class DocBlocks
                        {
                        /**
                         * Test constants
                         */
                            const INDENT = 1;
                        }

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before GeneralPhpdocAnnotationRemoveFixer, GeneralPhpdocTagRenameFixer, NoBlankLinesAfterPhpdocFixer, NoEmptyPhpdocFixer, NoSuperfluousPhpdocTagsFixer, PhpdocAddMissingParamAnnotationFixer, PhpdocAlignFixer, PhpdocAnnotationWithoutDotFixer, PhpdocArrayTypeFixer, PhpdocInlineTagNormalizerFixer, PhpdocLineSpanFixer, PhpdocListTypeFixer, PhpdocNoAccessFixer, PhpdocNoAliasTagFixer, PhpdocNoDuplicateTypesFixer, PhpdocNoEmptyReturnFixer, PhpdocNoPackageFixer, PhpdocNoUselessInheritdocFixer, PhpdocOrderByValueFixer, PhpdocOrderFixer, PhpdocParamOrderFixer, PhpdocReadonlyClassCommentToKeywordFixer, PhpdocReturnSelfReferenceFixer, PhpdocSeparationFixer, PhpdocSingleLineVarSpacingFixer, PhpdocSummaryFixer, PhpdocTagCasingFixer, PhpdocTagNoNamedArgumentsFixer, PhpdocTagTypeFixer, PhpdocToParamTypeFixer, PhpdocToPropertyTypeFixer, PhpdocToReturnTypeFixer, PhpdocTrimConsecutiveBlankLineSeparationFixer, PhpdocTrimFixer, PhpdocTypesFixer, PhpdocTypesNoDuplicatesFixer, PhpdocTypesOrderFixer, PhpdocVarAnnotationCorrectOrderFixer, PhpdocVarWithoutNameFixer.
     * Must run after IndentationTypeFixer, PhpdocToCommentFixer.
     */
    public function getPriority(): int
    {
        return 20;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; 0 <= $index; --$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($index);

            // skip if there is no next token or if next token is block end `}`
            if (null === $nextIndex || $tokens[$nextIndex]->equals('}')) {
                continue;
            }

            $prevIndex = $index - 1;
            $prevToken = $tokens[$prevIndex];

            // ignore inline docblocks
            if (
                $prevToken->isGivenKind(\T_OPEN_TAG)
                || ($prevToken->isWhitespace(" \t") && !$tokens[$index - 2]->isGivenKind(\T_OPEN_TAG))
                || $prevToken->equalsAny([';', ',', '{', '('])
            ) {
                continue;
            }

            if ($tokens[$nextIndex - 1]->isWhitespace()) {
                $indent = Utils::calculateTrailingWhitespaceIndent($tokens[$nextIndex - 1]);
            } else {
                $indent = '';
            }

            $newPrevContent = $this->fixWhitespaceBeforeDocblock($prevToken->getContent(), $indent);

            $tokens[$index] = new Token([\T_DOC_COMMENT, $this->fixDocBlock($token->getContent(), $indent)]);

            if (!$prevToken->isWhitespace()) {
                if ('' !== $indent) {
                    $tokens->insertAt($index, new Token([\T_WHITESPACE, $indent]));
                }
            } elseif ('' !== $newPrevContent) {
                if ($prevToken->isArray()) {
                    $tokens[$prevIndex] = new Token([$prevToken->getId(), $newPrevContent]);
                } else {
                    $tokens[$prevIndex] = new Token($newPrevContent);
                }
            } else {
                $tokens->clearAt($prevIndex);
            }
        }
    }

    /**
     * Fix indentation of Docblock.
     *
     * @param string $content Docblock contents
     * @param string $indent  Indentation to apply
     *
     * @return string Dockblock contents including correct indentation
     */
    private function fixDocBlock(string $content, string $indent): string
    {
        return ltrim(Preg::replace('/^\h*\*/m', $indent.' *', $content));
    }

    /**
     * @param string $content Whitespace before Docblock
     * @param string $indent  Indentation of the documented subject
     *
     * @return string Whitespace including correct indentation for Dockblock after this whitespace
     */
    private function fixWhitespaceBeforeDocblock(string $content, string $indent): string
    {
        return rtrim($content, " \t").$indent;
    }
}
