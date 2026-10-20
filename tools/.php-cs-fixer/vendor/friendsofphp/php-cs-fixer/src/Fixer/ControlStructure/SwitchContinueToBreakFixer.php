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
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SwitchContinueToBreakFixer extends AbstractFixer
{
    /**
     * @var list<int>
     */
    private array $switchLevels = [];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Switch case must not be ended with `continue` but with `break`.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        switch ($foo) {
                            case 1:
                                continue;
                        }

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        switch ($foo) {
                            case 1:
                                while($bar) {
                                    do {
                                        continue 3;
                                    } while(false);

                                    if ($foo + 1 > 3) {
                                        continue;
                                    }

                                    continue 2;
                                }
                        }

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after NoAlternativeSyntaxFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_SWITCH, \T_CONTINUE]) && !$tokens->hasAlternativeSyntax();
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $count = \count($tokens);

        for ($index = 1; $index < $count - 1; ++$index) {
            $index = $this->doFix($tokens, $index, 0, false);
        }
    }

    /**
     * @param int $depth >= 0
     */
    private function doFix(Tokens $tokens, int $index, int $depth, bool $isInSwitch): int
    {
        $token = $tokens[$index];

        if ($token->isGivenKind([\T_FOREACH, \T_FOR, \T_WHILE])) {
            // go to first `(`, go to its close ')', go to first of '{', ';', '? >'
            $index = $tokens->getNextTokenOfKind($index, ['(']);
            $index = $tokens->getNextTokenOfKind($index, [')']);
            $index = $tokens->getNextTokenOfKind($index, ['{', ';', [\T_CLOSE_TAG]]);

            if (!$tokens[$index]->equals('{')) {
                return $index;
            }

            return $this->fixInLoop($tokens, $index, $depth + 1);
        }

        if ($token->isGivenKind(\T_DO)) {
            return $this->fixInLoop($tokens, $tokens->getNextTokenOfKind($index, ['{']), $depth + 1);
        }

        if ($token->isGivenKind(\T_SWITCH)) {
            return $this->fixInSwitch($tokens, $index, $depth + 1);
        }

        if ($token->isGivenKind(\T_CONTINUE)) {
            return $this->fixContinueWhenActsAsBreak($tokens, $index, $isInSwitch, $depth);
        }

        return $index;
    }

    private function fixInSwitch(Tokens $tokens, int $switchIndex, int $depth): int
    {
        $this->switchLevels[] = $depth;

        // figure out where the switch starts
        $openIndex = $tokens->getNextTokenOfKind($switchIndex, ['{']);

        // figure out where the switch ends
        $closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $openIndex);

        for ($index = $openIndex + 1; $index < $closeIndex; ++$index) {
            $index = $this->doFix($tokens, $index, $depth, true);
        }

        array_pop($this->switchLevels);

        return $closeIndex;
    }

    private function fixInLoop(Tokens $tokens, int $openIndex, int $depth): int
    {
        $openCount = 1;

        while (true) {
            ++$openIndex;
            $token = $tokens[$openIndex];

            if ($token->equals('{')) {
                ++$openCount;

                continue;
            }

            if ($token->equals('}')) {
                --$openCount;

                if (0 === $openCount) {
                    break;
                }

                continue;
            }

            $openIndex = $this->doFix($tokens, $openIndex, $depth, false);
        }

        return $openIndex;
    }

    private function fixContinueWhenActsAsBreak(Tokens $tokens, int $continueIndex, bool $isInSwitch, int $depth): int
    {
        $followingContinueIndex = $tokens->getNextMeaningfulToken($continueIndex);
        $followingContinueToken = $tokens[$followingContinueIndex];

        if ($isInSwitch && $followingContinueToken->equals(';')) {
            $this->replaceContinueWithBreakToken($tokens, $continueIndex); // short continue 1 notation

            return $followingContinueIndex;
        }

        if (!$followingContinueToken->isGivenKind(\T_LNUMBER)) {
            return $followingContinueIndex;
        }

        $afterFollowingContinueIndex = $tokens->getNextMeaningfulToken($followingContinueIndex);

        if (!$tokens[$afterFollowingContinueIndex]->equals(';')) {
            return $afterFollowingContinueIndex; // if next not is `;` return without fixing, for example `continue 1 ? ><?php + $a;`
        }

        // check if continue {jump} targets a switch statement and if so fix it

        $jump = $followingContinueToken->getContent();
        $jump = str_replace('_', '', $jump); // support for numeric_literal_separator

        if (\strlen($jump) > 2 && 'x' === $jump[1]) {
            $jump = hexdec($jump); // hexadecimal - 0x1
        } elseif (\strlen($jump) > 2 && 'b' === $jump[1]) {
            $jump = bindec($jump); // binary - 0b1
        } elseif (\strlen($jump) > 1 && '0' === $jump[0]) {
            $jump = octdec($jump); // octal 01
        } elseif (Preg::match('#^\d+$#', $jump)) { // positive int
            $jump = (float) $jump; // cast to float, might be a number bigger than PHP max. int value
        } else {
            return $afterFollowingContinueIndex; // cannot process value, ignore
        }

        if ($jump > \PHP_INT_MAX) {
            return $afterFollowingContinueIndex; // cannot process value, ignore
        }

        $jump = (int) $jump;

        if ($isInSwitch && (1 === $jump || 0 === $jump)) {
            $this->replaceContinueWithBreakToken($tokens, $continueIndex); // long continue 0/1 notation

            return $afterFollowingContinueIndex;
        }

        $jumpDestination = $depth - $jump + 1;

        if (\in_array($jumpDestination, $this->switchLevels, true)) {
            $this->replaceContinueWithBreakToken($tokens, $continueIndex);

            return $afterFollowingContinueIndex;
        }

        return $afterFollowingContinueIndex;
    }

    private function replaceContinueWithBreakToken(Tokens $tokens, int $index): void
    {
        $tokens[$index] = new Token([\T_BREAK, 'break']);
    }
}
