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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class NoTrailingCommaInSinglelineFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'An element list written on one line cannot contain a trailing comma.',
            [
                new CodeSample("<?php\n\$x = ['foo', 'bar', ];\n"),
            ],
            '',
        );
    }

    /**
     * Must run after MethodArgumentSpaceFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN, CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE, '(']);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; $index--) {
            if (!$tokens[$index]->equalsAny([')', [CT::T_ARRAY_SQUARE_BRACE_CLOSE], [CT::T_DESTRUCTURING_SQUARE_BRACE_CLOSE]])) {
                continue;
            }

            $this->removeCommas($tokens, $index);
        }
    }

    private function removeCommas(Tokens $tokens, int $index): void
    {
        $commaIndex = $tokens->getPrevMeaningfulToken($index);
        \assert(\is_int($commaIndex));

        while ($tokens[$commaIndex]->equals(',')) {
            if ($tokens->isPartialCodeMultiline($commaIndex, $index)) {
                return;
            }

            $tokens->removeLeadingWhitespace($commaIndex);
            $tokens->removeTrailingWhitespace($commaIndex);
            $tokens->clearAt($commaIndex);

            $commaIndex = $tokens->getPrevMeaningfulToken($commaIndex);
            \assert(\is_int($commaIndex));
        }
    }
}
