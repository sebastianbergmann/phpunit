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

namespace PhpCsFixer\Fixer\Semicolon;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoEmptyStatementFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Remove useless (semicolon) statements.',
            [
                new CodeSample("<?php \$a = 1;;\n"),
                new CodeSample("<?php echo 1;2;\n"),
                new CodeSample("<?php while(foo()){\n    continue 1;\n}\n"),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before BracesFixer, CombineConsecutiveUnsetsFixer, EmptyLoopBodyFixer, MultilineWhitespaceBeforeSemicolonsFixer, NoExtraBlankLinesFixer, NoMultipleStatementsPerLineFixer, NoSinglelineWhitespaceBeforeSemicolonsFixer, NoTrailingWhitespaceFixer, NoUselessElseFixer, NoUselessReturnFixer, NoWhitespaceInBlankLineFixer, ReturnAssignmentFixer, SpaceAfterSemicolonFixer, SwitchCaseSemicolonToColonFixer.
     * Must run after NoUselessSprintfFixer.
     */
    public function getPriority(): int
    {
        return 40;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(';');
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
            if ($tokens[$index]->isGivenKind([\T_BREAK, \T_CONTINUE])) {
                $index = $tokens->getNextMeaningfulToken($index);

                if ($tokens[$index]->equals([\T_LNUMBER, '1'])) {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($index);
                }

                continue;
            }

            // skip T_FOR parenthesis to ignore double `;` like `for ($i = 1; ; ++$i) {...}`
            if ($tokens[$index]->isGivenKind(\T_FOR)) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $tokens->getNextMeaningfulToken($index)) + 1;

                continue;
            }

            if (!$tokens[$index]->equals(';')) {
                continue;
            }

            $previousMeaningfulIndex = $tokens->getPrevMeaningfulToken($index);

            // A semicolon can always be removed if it follows a semicolon, '{' or opening tag.
            if ($tokens[$previousMeaningfulIndex]->equalsAny(['{', ';', [\T_OPEN_TAG]])) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);

                continue;
            }

            // A semicolon might be removed if it follows a '}' but only if the brace is part of certain structures.
            if ($tokens[$previousMeaningfulIndex]->equals('}')) {
                $this->fixSemicolonAfterCurlyBraceClose($tokens, $index, $previousMeaningfulIndex);

                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($index);
            if (null !== $nextIndex && $tokens[$nextIndex]->isGivenKind(CT::T_PROPERTY_HOOK_BRACE_CLOSE)) {
                continue;
            }

            // A semicolon might be removed together with its noop statement, for example "<?php 1;"
            $prePreviousMeaningfulIndex = $tokens->getPrevMeaningfulToken($previousMeaningfulIndex);

            if (
                $tokens[$prePreviousMeaningfulIndex]->equalsAny([';', '{', '}', [\T_OPEN_TAG]])
                && $tokens[$previousMeaningfulIndex]->isGivenKind([\T_CONSTANT_ENCAPSED_STRING, \T_DNUMBER, \T_LNUMBER, \T_STRING, \T_VARIABLE])
            ) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
                $tokens->clearTokenAndMergeSurroundingWhitespace($previousMeaningfulIndex);
            }
        }
    }

    /**
     * Fix semicolon after closing curly brace if needed.
     *
     * Test for the following cases
     * - just '{' '}' block (following open tag or ';')
     * - if, else, elseif
     * - interface, trait, class (but not anonymous)
     * - catch, finally (but not try)
     * - for, foreach, while (but not 'do - while')
     * - switch
     * - function (declaration, but not lambda)
     * - declare (with '{' '}')
     * - namespace (with '{' '}')
     *
     * @param int $index Semicolon index
     */
    private function fixSemicolonAfterCurlyBraceClose(Tokens $tokens, int $index, int $curlyCloseIndex): void
    {
        $curlyOpeningIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_BRACE, $curlyCloseIndex);
        $beforeCurlyOpeningIndex = $tokens->getPrevMeaningfulToken($curlyOpeningIndex);

        if ($tokens[$beforeCurlyOpeningIndex]->isGivenKind([\T_ELSE, \T_FINALLY, \T_NAMESPACE, \T_OPEN_TAG]) || $tokens[$beforeCurlyOpeningIndex]->equalsAny([';', '{', '}'])) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($index);

            return;
        }

        // check for namespaces and class, interface and trait definitions
        if ($tokens[$beforeCurlyOpeningIndex]->isGivenKind(\T_STRING)) {
            $classyTestIndex = $tokens->getPrevMeaningfulToken($beforeCurlyOpeningIndex);

            while ($tokens[$classyTestIndex]->equals(',') || $tokens[$classyTestIndex]->isGivenKind([\T_STRING, \T_NS_SEPARATOR, \T_EXTENDS, \T_IMPLEMENTS])) {
                $classyTestIndex = $tokens->getPrevMeaningfulToken($classyTestIndex);
            }

            $tokensAnalyzer = new TokensAnalyzer($tokens);

            if (
                $tokens[$classyTestIndex]->isGivenKind(\T_NAMESPACE)
                || ($tokens[$classyTestIndex]->isClassy() && !$tokensAnalyzer->isAnonymousClass($classyTestIndex))
            ) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            }

            return;
        }

        // early return check, below only control structures with conditions are fixed
        if (!$tokens[$beforeCurlyOpeningIndex]->equals(')')) {
            return;
        }

        $openingBraceIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $beforeCurlyOpeningIndex);
        $beforeOpeningBraceIndex = $tokens->getPrevMeaningfulToken($openingBraceIndex);

        if ($tokens[$beforeOpeningBraceIndex]->isGivenKind([\T_IF, \T_ELSEIF, \T_FOR, \T_FOREACH, \T_WHILE, \T_SWITCH, \T_CATCH, \T_DECLARE])) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($index);

            return;
        }

        // check for function definition
        if ($tokens[$beforeOpeningBraceIndex]->isGivenKind(\T_STRING)) {
            $beforeStringIndex = $tokens->getPrevMeaningfulToken($beforeOpeningBraceIndex);

            if ($tokens[$beforeStringIndex]->isGivenKind(\T_FUNCTION)) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index); // implicit return
            }
        }
    }
}
