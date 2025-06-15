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
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class NoNullableBooleanTypeFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no nullable boolean types.',
            [new CodeSample('<?php
function foo(?bool $bar) : ?bool
{
     return $bar;
 }
')],
            '',
            'when the null is used',
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_STRING);
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if ($tokens[$index]->getContent() !== '?') {
                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($nextIndex));

            if (!$tokens[$nextIndex]->equals([\T_STRING, 'bool'], false) && !$tokens[$nextIndex]->equals([\T_STRING, 'boolean'], false)) {
                continue;
            }

            $nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);
            \assert(\is_int($nextNextIndex));

            if (!$tokens[$nextNextIndex]->isGivenKind(\T_VARIABLE) && $tokens[$nextNextIndex]->getContent() !== '{') {
                continue;
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($index);
        }
    }
}
