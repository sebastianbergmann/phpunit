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

namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Jared Henderson <jared@netrivet.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TrimArraySpacesFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Arrays should be formatted like function/method arguments, without leading or trailing single line space.',
            [new CodeSample("<?php\n\$sample = array( );\n\$sample = array( 'a', 'b' );\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_ARRAY, CT::T_ARRAY_BRACKET_OPEN, CT::T_DESTRUCTURING_BRACKET_OPEN]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0, $c = $tokens->count(); $index < $c; ++$index) {
            if ($tokens[$index]->isGivenKind([\T_ARRAY, CT::T_ARRAY_BRACKET_OPEN, CT::T_DESTRUCTURING_BRACKET_OPEN])) {
                self::fixArray($tokens, $index);
            }
        }
    }

    /**
     * Method to trim leading/trailing whitespace within single line arrays.
     */
    private static function fixArray(Tokens $tokens, int $index): void
    {
        $startIndex = $index;

        if ($tokens[$startIndex]->isGivenKind(\T_ARRAY)) {
            $startIndex = $tokens->getNextMeaningfulToken($startIndex);
            $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $startIndex);
        } elseif ($tokens[$startIndex]->isGivenKind(CT::T_DESTRUCTURING_BRACKET_OPEN)) {
            $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_DESTRUCTURING_BRACKET, $startIndex);
        } else {
            $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $startIndex);
        }

        $nextIndex = $startIndex + 1;
        $nextToken = $tokens[$nextIndex];
        $nextNonWhitespaceIndex = $tokens->getNextNonWhitespace($startIndex);
        $nextNonWhitespaceToken = $tokens[$nextNonWhitespaceIndex];
        $tokenAfterNextNonWhitespaceToken = $tokens[$nextNonWhitespaceIndex + 1];

        $prevIndex = $endIndex - 1;
        $prevToken = $tokens[$prevIndex];
        $prevNonWhitespaceIndex = $tokens->getPrevNonWhitespace($endIndex);
        $prevNonWhitespaceToken = $tokens[$prevNonWhitespaceIndex];

        if (
            $nextToken->isWhitespace(" \t")
            && (
                !$nextNonWhitespaceToken->isComment()
                || $nextNonWhitespaceIndex === $prevNonWhitespaceIndex
                || $tokenAfterNextNonWhitespaceToken->isWhitespace(" \t")
                || str_starts_with($nextNonWhitespaceToken->getContent(), '/*')
            )
        ) {
            $tokens->clearAt($nextIndex);
        }

        if (
            $prevToken->isWhitespace(" \t")
            && !$prevNonWhitespaceToken->equals(',')
        ) {
            $tokens->clearAt($prevIndex);
        }
    }
}
