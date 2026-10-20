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

namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class CombineConsecutiveUnsetsFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Calling `unset` on multiple items should be done in one call.',
            [new CodeSample("<?php\nunset(\$a); unset(\$b);\n")],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before NoExtraBlankLinesFixer, NoTrailingWhitespaceFixer, NoWhitespaceInBlankLineFixer, SpaceAfterSemicolonFixer.
     * Must run after NoEmptyStatementFixer, NoUnsetOnPropertyFixer, NoUselessElseFixer.
     */
    public function getPriority(): int
    {
        return 24;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_UNSET);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_UNSET)) {
                continue;
            }

            $previousUnsetCall = $this->getPreviousUnsetCall($tokens, $index);
            if (\is_int($previousUnsetCall)) {
                $index = $previousUnsetCall;

                continue;
            }

            [$previousUnset, , $previousUnsetBraceEnd] = $previousUnsetCall;

            // Merge the tokens inside the 'unset' call into the previous one 'unset' call.
            $tokensAddCount = $this->moveTokens(
                $tokens,
                $nextUnsetContentStart = $tokens->getNextTokenOfKind($index, ['(']),
                $nextUnsetContentEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $nextUnsetContentStart),
                $previousUnsetBraceEnd - 1,
            );

            if (!$tokens[$previousUnsetBraceEnd]->isWhitespace()) {
                $tokens->insertAt($previousUnsetBraceEnd, new Token([\T_WHITESPACE, ' ']));
                ++$tokensAddCount;
            }

            $tokenBeforePreviousUnsetBraceEnd = $tokens->getPrevMeaningfulToken($previousUnsetBraceEnd);

            if (!$tokens[$tokenBeforePreviousUnsetBraceEnd]->equals(',')) {
                $tokens->insertAt($previousUnsetBraceEnd, new Token(','));
                ++$tokensAddCount;
            } elseif ($tokens[$tokenBeforePreviousUnsetBraceEnd + 1]->isWhitespace()) {
                // keeping trailing comma from previous `unset`, but tokens moved - may cause 2 whitespaces tokens one after another - needed to clean this up
                $tokens->clearTokenAndMergeSurroundingWhitespace($tokenBeforePreviousUnsetBraceEnd + 1);
            }

            // Remove 'unset', '(', ')' and (possibly) ';' from the merged 'unset' call.
            $this->clearOffsetTokens($tokens, $tokensAddCount, [$index, $nextUnsetContentStart, $nextUnsetContentEnd]);

            $nextUnsetSemicolon = $tokens->getNextMeaningfulToken($nextUnsetContentEnd);
            if (null !== $nextUnsetSemicolon && $tokens[$nextUnsetSemicolon]->equals(';')) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($nextUnsetSemicolon);
            }

            $index = $previousUnset + 1;
        }
    }

    /**
     * @param list<int> $indices
     */
    private function clearOffsetTokens(Tokens $tokens, int $offset, array $indices): void
    {
        foreach ($indices as $index) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($index + $offset);
        }
    }

    /**
     * Find a previous call to unset directly before the index.
     *
     * Returns an array with
     * * unset index
     * * opening brace index
     * * closing brace index
     * * end semicolon index
     *
     * Or the index to where the method looked for a call.
     *
     * @return array{int, int, int, int}|int
     */
    private function getPreviousUnsetCall(Tokens $tokens, int $index)
    {
        $previousUnsetSemicolon = $tokens->getPrevMeaningfulToken($index);
        if (null === $previousUnsetSemicolon) {
            return $index;
        }

        if (!$tokens[$previousUnsetSemicolon]->equals(';')) {
            return $previousUnsetSemicolon;
        }

        $previousUnsetBraceEnd = $tokens->getPrevMeaningfulToken($previousUnsetSemicolon);
        if (null === $previousUnsetBraceEnd) {
            return $index;
        }

        if (!$tokens[$previousUnsetBraceEnd]->equals(')')) {
            return $previousUnsetBraceEnd;
        }

        $previousUnsetBraceStart = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $previousUnsetBraceEnd);
        $previousUnset = $tokens->getPrevMeaningfulToken($previousUnsetBraceStart);
        if (null === $previousUnset) {
            return $index;
        }

        if (!$tokens[$previousUnset]->isGivenKind(\T_UNSET)) {
            return $previousUnset;
        }

        return [
            $previousUnset,
            $previousUnsetBraceStart,
            $previousUnsetBraceEnd,
            $previousUnsetSemicolon,
        ];
    }

    /**
     * @param int $start Index previous of the first token to move
     * @param int $end   Index of the last token to move
     * @param int $to    Upper boundary index
     *
     * @return int Number of tokens inserted
     */
    private function moveTokens(Tokens $tokens, int $start, int $end, int $to): int
    {
        $added = 0;
        for ($i = $start + 1; $i < $end; $i += 2) {
            if ($tokens[$i]->isWhitespace() && $tokens[$to + 1]->isWhitespace()) {
                $tokens[$to + 1] = new Token([\T_WHITESPACE, $tokens[$to + 1]->getContent().$tokens[$i]->getContent()]);
            } else {
                $tokens->insertAt(++$to, clone $tokens[$i]);
                ++$end;
                ++$added;
            }

            $tokens->clearAt($i + 1);
        }

        return $added;
    }
}
