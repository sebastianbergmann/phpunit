<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class CommentSurroundedBySpacesFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Comments must be surrounded by spaces.',
            [new CodeSample('<?php
/*foo*/
')],
            '',
        );
    }

    /**
     * Must run before MultilineCommentOpeningClosingFixer, PhpdocToCommentFixer.
     * Must run after CommentedOutFunctionFixer.
     */
    public function getPriority(): int
    {
        return 26;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_COMMENT, \T_DOC_COMMENT]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind([\T_COMMENT, \T_DOC_COMMENT])) {
                continue;
            }

            // ensure whitespace at the beginning
            $newContent = Preg::replace(
                '/^(\\/\\/|#(?!\\[)|\\/\\*+)(?!(?:\\/|\\*|\\s|$))/',
                '$1 ',
                $tokens[$index]->getContent(),
            );

            // ensure whitespace at the end
            $newContent = Preg::replace(
                '/(?<!(?:\\/|\\*|\\h))(\\*+\\/)$/',
                ' $1',
                $newContent,
            );

            if ($newContent === $tokens[$index]->getContent()) {
                continue;
            }

            $tokens[$index] = new Token([\strpos($newContent, '/** ') === 0 ? \T_DOC_COMMENT : \T_COMMENT, $newContent]);
        }
    }
}
