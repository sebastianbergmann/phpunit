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

use PhpCsFixer\AbstractNoUselessElseFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoUselessElseFixer extends AbstractNoUselessElseFixer
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_ELSE);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should not be useless `else` cases.',
            [
                new CodeSample("<?php\nif (\$a) {\n    return 1;\n} else {\n    return 2;\n}\n"),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before BlankLineBeforeStatementFixer, BracesFixer, CombineConsecutiveUnsetsFixer, NoBreakCommentFixer, NoExtraBlankLinesFixer, NoTrailingWhitespaceFixer, NoUselessReturnFixer, NoWhitespaceInBlankLineFixer, SimplifiedIfReturnFixer, StatementIndentationFixer.
     * Must run after NoAlternativeSyntaxFixer, NoEmptyStatementFixer, NoUnneededBracesFixer, NoUnneededCurlyBracesFixer.
     */
    public function getPriority(): int
    {
        return parent::getPriority();
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_ELSE)) {
                continue;
            }

            // `else if` vs. `else` and alternative syntax `else:` checks
            if ($tokens[$tokens->getNextMeaningfulToken($index)]->equalsAny([':', [\T_IF]])) {
                continue;
            }

            // clean up `else` if it is an empty statement
            $this->fixEmptyElse($tokens, $index);
            if ($tokens->isEmptyAt($index)) {
                continue;
            }

            // Clean up `else` if possible.
            // Ignore `else` blocks containing named function or classy declarations, because in PHP function/class
            // declarations outside any conditional block are always evaluated first, even if the code before the declaration
            // returns/throws/etc. So removing such `else` blocks would change the behaviour.
            if ($this->isSuperfluousElse($tokens, $index) && !$this->containsNamedSymbolDeclaration($tokens, $index)) {
                $this->clearElse($tokens, $index);
            }
        }
    }

    /**
     * Remove tokens part of an `else` statement if not empty (i.e. no meaningful tokens inside).
     *
     * @param int $index T_ELSE index
     */
    private function fixEmptyElse(Tokens $tokens, int $index): void
    {
        $next = $tokens->getNextMeaningfulToken($index);

        if ($tokens[$next]->equals('{')) {
            $close = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $next);
            if (1 === $close - $next) { // '{}'
                $this->clearElse($tokens, $index);
            } elseif ($tokens->getNextMeaningfulToken($next) === $close) { // '{/**/}'
                $this->clearElse($tokens, $index);
            }

            return;
        }

        // short `else`
        $end = $tokens->getNextTokenOfKind($index, [';', [\T_CLOSE_TAG]]);
        if ($next === $end) {
            $this->clearElse($tokens, $index);
        }
    }

    /**
     * @param int $index index of T_ELSE
     */
    private function clearElse(Tokens $tokens, int $index): void
    {
        $tokens->clearTokenAndMergeSurroundingWhitespace($index);

        // clear T_ELSE and the '{' '}' if there are any
        $next = $tokens->getNextMeaningfulToken($index);

        if (!$tokens[$next]->equals('{')) {
            return;
        }

        $tokens->clearTokenAndMergeSurroundingWhitespace($tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $next));
        $tokens->clearTokenAndMergeSurroundingWhitespace($next);
    }

    /**
     * @param int $index index of T_ELSE
     */
    private function containsNamedSymbolDeclaration(Tokens $tokens, int $index): bool
    {
        $next = $tokens->getNextMeaningfulToken($index);

        if (!$tokens[$next]->equals('{')) {
            // short `else` can't contain symbol declaration (`else class Foo {}` is invalid syntax)
            return false;
        }

        $tokensAnalyzer = new TokensAnalyzer($tokens);
        $close = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $next);
        for ($i = $next + 1; $i < $close; ++$i) {
            if ($tokens[$i]->isGivenKind(\T_FUNCTION) && !$tokensAnalyzer->isLambda($i)) {
                return true;
            }

            if ($tokens[$i]->isClassy() && !$tokensAnalyzer->isAnonymousClass($i)) {
                return true;
            }
        }

        return false;
    }
}
