<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers;

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 */
final class TokenRemover
{
    public static function removeWithLinesIfPossible(Tokens $tokens, int $index): void
    {
        if (self::isTokenOnlyMeaningfulInLine($tokens, $index)) {
            $prevIndex = $tokens->getNonEmptySibling($index, -1);
            \assert(\is_int($prevIndex));

            $wasNewlineRemoved = self::handleWhitespaceBefore($tokens, $prevIndex);

            $nextIndex = $tokens->getNonEmptySibling($index, 1);
            if ($nextIndex !== null) {
                self::handleWhitespaceAfter($tokens, $nextIndex, $wasNewlineRemoved);
            }
        }

        $tokens->clearTokenAndMergeSurroundingWhitespace($index);
    }

    private static function isTokenOnlyMeaningfulInLine(Tokens $tokens, int $index): bool
    {
        return !self::hasMeaningTokenInLineBefore($tokens, $index) && !self::hasMeaningTokenInLineAfter($tokens, $index);
    }

    private static function hasMeaningTokenInLineBefore(Tokens $tokens, int $index): bool
    {
        $prevIndex = $tokens->getNonEmptySibling($index, -1);
        \assert(\is_int($prevIndex));

        if (!$tokens[$prevIndex]->isGivenKind([\T_OPEN_TAG, \T_WHITESPACE])) {
            return true;
        }

        if ($tokens[$prevIndex]->isGivenKind(\T_OPEN_TAG) && !Preg::match('/\\R$/', $tokens[$prevIndex]->getContent())) {
            return true;
        }

        if (!Preg::match('/\\R/', $tokens[$prevIndex]->getContent())) {
            $prevPrevIndex = $tokens->getNonEmptySibling($prevIndex, -1);
            \assert(\is_int($prevPrevIndex));

            if (!$tokens[$prevPrevIndex]->isGivenKind(\T_OPEN_TAG) || !Preg::match('/\\R$/', $tokens[$prevPrevIndex]->getContent())) {
                return true;
            }
        }

        return false;
    }

    private static function hasMeaningTokenInLineAfter(Tokens $tokens, int $index): bool
    {
        $nextIndex = $tokens->getNonEmptySibling($index, 1);
        if ($nextIndex === null) {
            return false;
        }

        if (!$tokens[$nextIndex]->isGivenKind(\T_WHITESPACE)) {
            return true;
        }

        return !Preg::match('/\\R/', $tokens[$nextIndex]->getContent());
    }

    private static function handleWhitespaceBefore(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->isGivenKind(\T_WHITESPACE)) {
            return false;
        }
        $contentWithoutTrailingSpaces = Preg::replace('/\\h+$/', '', $tokens[$index]->getContent());

        $contentWithoutTrailingSpacesAndNewline = Preg::replace('/\\R$/', '', $contentWithoutTrailingSpaces, 1);

        $tokens->ensureWhitespaceAtIndex($index, 0, $contentWithoutTrailingSpacesAndNewline);

        return $contentWithoutTrailingSpaces !== $contentWithoutTrailingSpacesAndNewline;
    }

    private static function handleWhitespaceAfter(Tokens $tokens, int $index, bool $wasNewlineRemoved): void
    {
        $pattern = $wasNewlineRemoved ? '/^\\h+/' : '/^\\h*\\R/';

        $newContent = Preg::replace($pattern, '', $tokens[$index]->getContent());

        $tokens->ensureWhitespaceAtIndex($index, 0, $newContent);
    }
}
