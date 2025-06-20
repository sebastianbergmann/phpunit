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
final class TrimKeyFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The string key of an array or generator must be trimmed and have no double spaces.',
            [new CodeSample(<<<'PHP'
                <?php
                $array = [
                    'option 1 ' => 'v1',
                    'option 2  or 3' => 'v23',
                ];

                PHP)],
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_CONSTANT_ENCAPSED_STRING]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_DOUBLE_ARROW)) {
                continue;
            }

            $indexToFix = $tokens->getPrevMeaningfulToken($index);
            \assert(\is_int($indexToFix));

            if (!$tokens[$indexToFix]->isGivenKind([\T_CONSTANT_ENCAPSED_STRING])) {
                continue;
            }

            $content = $tokens[$indexToFix]->getContent();
            $stringBorderQuote = $content[0];
            $innerContent = \substr($content, 1, -1);

            $newInnerContent = Preg::replace('/\\s{2,}/', ' ', $innerContent);

            $prevIndex = $tokens->getPrevMeaningfulToken($indexToFix);
            if (!$tokens[$prevIndex]->equals('.')) {
                $newInnerContent = \ltrim($newInnerContent);
            }

            $nextIndex = $tokens->getNextMeaningfulToken($indexToFix);
            if (!$tokens[$nextIndex]->equals('.')) {
                $newInnerContent = \rtrim($newInnerContent);
            }

            if ($newInnerContent === '') {
                continue;
            }

            $newContent = $stringBorderQuote . $newInnerContent . $stringBorderQuote;

            if ($content === $newContent) {
                continue;
            }

            $tokens[$indexToFix] = new Token([\T_CONSTANT_ENCAPSED_STRING, $newContent]);
        }
    }
}
