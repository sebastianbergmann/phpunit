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

/**
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpdocTrimFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHPDoc should start and end with content, excluding the very first and last line of the docblocks.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        /**
                         *
                         * Foo must be final class.
                         *
                         *
                         */
                        final class Foo {}

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, GeneralPhpdocAnnotationRemoveFixer, PhpUnitAttributesFixer, PhpUnitTestAnnotationFixer, PhpdocIndentFixer, PhpdocNoAccessFixer, PhpdocNoEmptyReturnFixer, PhpdocNoPackageFixer, PhpdocOrderFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority(): int
    {
        return -5;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }

            $content = $token->getContent();
            $content = $this->fixStart($content);
            // we need re-parse the docblock after fixing the start before
            // fixing the end in order for the lines to be correctly indexed
            $content = $this->fixEnd($content);
            $tokens[$index] = new Token([\T_DOC_COMMENT, $content]);
        }
    }

    /**
     * Make sure the first useful line starts immediately after the first line.
     */
    private function fixStart(string $content): string
    {
        return Preg::replace(
            '~
                (^/\*\*)            # DocComment begin
                (?:
                    \R\h*(?:\*\h*)? # lines without useful content
                    (?!\R\h*\*/)    # not followed by a DocComment end
                )+
                (\R\h*(?:\*\h*)?\S) # first line with useful content
            ~x',
            '$1$2',
            $content,
        );
    }

    /**
     * Make sure the last useful line is immediately before the final line.
     */
    private function fixEnd(string $content): string
    {
        return Preg::replace(
            '~
                (\R\h*(?:\*\h*)?\S.*?) # last line with useful content
                (?:
                    (?<!/\*\*)         # not preceded by a DocComment start
                    \R\h*(?:\*\h*)?    # lines without useful content
                )+
                (\R\h*\*/$)            # DocComment end
            ~xu',
            '$1$2',
            $content,
        );
    }
}
