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

namespace PhpCsFixer\Fixer\ReturnNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoUselessReturnFixer extends AbstractFixer
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_FUNCTION, \T_RETURN]);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should not be an empty `return` statement at the end of a function.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        function example($b) {
                            if ($b) {
                                return;
                            }
                            return;
                        }

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before BlankLineBeforeStatementFixer, NoExtraBlankLinesFixer, NoWhitespaceInBlankLineFixer, SingleLineCommentStyleFixer, SingleLineEmptyBodyFixer.
     * Must run after NoEmptyStatementFixer, NoUnneededBracesFixer, NoUnneededCurlyBracesFixer, NoUselessElseFixer, SimplifiedNullReturnFixer.
     */
    public function getPriority(): int
    {
        return -18;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_FUNCTION)) {
                continue;
            }

            $index = $tokens->getNextTokenOfKind($index, [';', '{']);
            if ($tokens[$index]->equals('{')) {
                $this->fixFunction($tokens, $index, $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $index));
            }
        }
    }

    /**
     * @param int $start Token index of the opening brace token of the function
     * @param int $end   Token index of the closing brace token of the function
     */
    private function fixFunction(Tokens $tokens, int $start, int $end): void
    {
        for ($index = $end; $index > $start; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_RETURN)) {
                continue;
            }

            $nextAt = $tokens->getNextMeaningfulToken($index);
            if (!$tokens[$nextAt]->equals(';')) {
                continue;
            }

            if ($tokens->getNextMeaningfulToken($nextAt) !== $end) {
                continue;
            }

            $previous = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$previous]->equalsAny([[\T_ELSE], ')'])) {
                continue;
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            $tokens->clearTokenAndMergeSurroundingWhitespace($nextAt);
        }
    }
}
