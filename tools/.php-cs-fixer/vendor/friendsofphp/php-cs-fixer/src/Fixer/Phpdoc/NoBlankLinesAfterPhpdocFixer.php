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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoBlankLinesAfterPhpdocFixer extends AbstractFixer
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should not be blank lines between docblock and the documented element.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php

                        /**
                         * This is the bar class.
                         */


                        class Bar {}

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before HeaderCommentFixer, PhpdocAlignFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority(): int
    {
        return -20;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            // get the next non-whitespace token inc comments, provided
            // that there is whitespace between it and the current token
            $next = $tokens->getNextNonWhitespace($index);
            if ($index + 2 === $next && false === $tokens[$next]->isGivenKind([
                \T_BREAK,
                \T_COMMENT,
                \T_CONTINUE,
                \T_DECLARE,
                \T_DOC_COMMENT,
                \T_GOTO,
                \T_INCLUDE,
                \T_INCLUDE_ONCE,
                \T_NAMESPACE,
                \T_REQUIRE,
                \T_REQUIRE_ONCE,
                \T_RETURN,
                \T_THROW,
                \T_USE,
                \T_WHITESPACE,
            ])) {
                $this->fixWhitespace($tokens, $index + 1);
            }
        }
    }

    /**
     * Cleanup a whitespace token.
     */
    private function fixWhitespace(Tokens $tokens, int $index): void
    {
        $content = $tokens[$index]->getContent();
        // if there is more than one new line in the whitespace, then we need to fix it
        if (substr_count($content, "\n") > 1) {
            // the final bit of the whitespace must be the next statement's indentation
            \assert(false !== strrpos($content, "\n"));
            $tokens[$index] = new Token([\T_WHITESPACE, substr($content, strrpos($content, "\n"))]);
        }
    }
}
