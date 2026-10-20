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

namespace PhpCsFixer\Fixer\ControlStructure;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @phpstan-import-type _PhpTokenPrototypePartial from Token
 *
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SimplifiedIfReturnFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Simplify `if` control structures that return the boolean result of their condition.',
            [new CodeSample("<?php\nif (\$foo) { return true; } return false;\n")],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after NoSuperfluousElseifFixer, NoUnneededBracesFixer, NoUnneededCurlyBracesFixer, NoUselessElseFixer, SemicolonAfterInstructionFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_IF, \T_RETURN, \T_STRING]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $slices = [];

        for ($ifIndex = $tokens->count() - 1; 0 <= $ifIndex; --$ifIndex) {
            $id = $tokens[$ifIndex]->getId();

            // Use direct ID comparisons here; this loop runs for every token and profiling
            // showed it to be measurably faster than Token::isGivenKind().
            if (\T_IF !== $id && \T_ELSEIF !== $id) {
                continue;
            }

            if ($tokens[$tokens->getPrevMeaningfulToken($ifIndex)]->equals(')')) {
                continue; // in a loop without braces
            }

            $startParenthesisIndex = $tokens->getNextTokenOfKind($ifIndex, ['(']);
            $endParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $startParenthesisIndex);
            $firstCandidateIndex = $tokens->getNextMeaningfulToken($endParenthesisIndex);

            $match = $this->matchReturnSequence($tokens, $firstCandidateIndex);

            if (null === $match) {
                continue;
            }

            $indicesToClear = $match['indices'];
            $lastSemicolonIndex = array_pop($indicesToClear); // Preserve last semicolon
            rsort($indicesToClear);

            foreach ($indicesToClear as $index) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            }
            $tokens->removeLeadingWhitespace($lastSemicolonIndex);

            $newTokens = [
                new Token([\T_RETURN, 'return']),
                new Token([\T_WHITESPACE, ' ']),
                $match['isNegative']
                    ? new Token('!')
                    : new Token([\T_BOOL_CAST, '(bool)']),
            ];

            $slices[$ifIndex] = $newTokens;
            $tokens->clearAt($ifIndex);
        }

        if ([] !== $slices) {
            $tokens->insertSlices($slices);
        }
    }

    /**
     * @return null|array{isNegative: bool, indices: list{0: int, 1: int, 2: int, 3: int, 4: int, 5: int, 6?: int, 7?: int}}
     */
    private function matchReturnSequence(Tokens $tokens, int $start): ?array
    {
        $indices = [];

        if ($tokens[$start]->equals('{')) {
            $indices[] = $start;

            $start = $tokens->getNextMeaningfulToken($start);
            \assert(null !== $start); // token must exist otherwise $tokens is not valid syntax
        }

        // searching for first return

        if (\T_RETURN !== $tokens[$start]->getId()) {
            return null;
        }

        $indices[] = $start;

        $bool1 = $tokens->getNextMeaningfulToken($start);
        \assert(null !== $bool1); // token must exist otherwise $tokens is not a valid syntax

        if (!$tokens[$bool1]->equalsAny([[\T_STRING, 'true'], [\T_STRING, 'false']])) {
            // not a boolean
            return null;
        }

        $value1 = $tokens[$bool1]->getContent();

        $semi1 = $tokens->getNextMeaningfulToken($bool1);
        \assert(null !== $semi1); // token must exist otherwise $tokens is not valid syntax

        if (';' !== $tokens[$semi1]->getContent()) {
            return null;
        }

        $indices[] = $bool1;
        $indices[] = $semi1;

        $next = $tokens->getNextMeaningfulToken($semi1);
        if (null === $next) {
            return null;
        }

        if ($tokens[$next]->equals('}')) {
            $indices[] = $next;

            $next = $tokens->getNextMeaningfulToken($next);
            if (null === $next) {
                return null;
            }
        }

        // searching for second return
        $isNegative = 'false' === $value1;

        if (\T_RETURN !== $tokens[$next]->getId()) {
            return null;
        }

        $indices[] = $next;

        $bool2 = $tokens->getNextMeaningfulToken($next);
        \assert(null !== $bool2); // token must exist otherwise $tokens is not valid syntax

        if (!$tokens[$bool2]->equals([\T_STRING, $isNegative ? 'true' : 'false'])) {
            // not a boolean opposite to one in 1st `return` statement
            return null;
        }

        $semi2 = $tokens->getNextMeaningfulToken($bool2);
        \assert(null !== $semi2); // token must exist otherwise $tokens is not valid syntax

        if (';' !== $tokens[$semi2]->getContent()) {
            return null;
        }

        $indices[] = $bool2;
        $indices[] = $semi2;

        return [
            'isNegative' => $isNegative,
            'indices' => $indices,
        ];
    }
}
