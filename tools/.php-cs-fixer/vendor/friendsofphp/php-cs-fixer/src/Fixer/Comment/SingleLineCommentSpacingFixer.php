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

namespace PhpCsFixer\Fixer\Comment;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SingleLineCommentSpacingFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Single-line comments must have proper spacing.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        //comment 1
                        #comment 2
                        /*comment 3*/

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after PhpdocToCommentFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_COMMENT);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = \count($tokens) - 1; 0 <= $index; --$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind(\T_COMMENT)) {
                continue;
            }

            $content = $token->getContent();
            $contentLength = \strlen($content);

            if ('/' === $content[0]) {
                if ($contentLength < 3) {
                    continue; // cheap check for "//"
                }

                if ('*' === $content[1]) { // slash asterisk comment
                    if ($contentLength < 5 || '*' === $content[2] || str_contains($content, "\n")) {
                        continue; // cheap check for "/**/", comment that looks like a PHPDoc, or multi line comment
                    }

                    $newContent = rtrim(substr($content, 0, -2)).' '.substr($content, -2);
                    $newContent = $this->fixCommentLeadingSpace($newContent, '/*');
                } else { // double slash comment
                    $newContent = $this->fixCommentLeadingSpace($content, '//');
                }
            } else { // hash comment
                if ($contentLength < 2 || '[' === $content[1]) { // cheap check for "#" or annotation (like) comment
                    continue;
                }

                $newContent = $this->fixCommentLeadingSpace($content, '#');
            }

            if ($newContent !== $content) {
                $tokens[$index] = new Token([\T_COMMENT, $newContent]);
            }
        }
    }

    // fix space between comment open and leading text
    private function fixCommentLeadingSpace(string $content, string $prefix): string
    {
        if (Preg::match(\sprintf('@^%s\h+.*$@', preg_quote($prefix, '@')), $content)) {
            return $content;
        }

        $position = \strlen($prefix);

        return substr($content, 0, $position).' '.substr($content, $position);
    }
}
