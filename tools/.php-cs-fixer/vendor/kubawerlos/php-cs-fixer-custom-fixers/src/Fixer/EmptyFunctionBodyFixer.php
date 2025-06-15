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
final class EmptyFunctionBodyFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Empty function body must be abbreviated as `{}` and placed on the same line as the previous symbol, separated with a space.',
            [new CodeSample('<?php function foo(
    int $x
)
{
}
')],
            '',
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_FUNCTION);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_FUNCTION)) {
                continue;
            }

            $openBraceIndex = $tokens->getNextTokenOfKind($index, ['{', ';']);
            \assert(\is_int($openBraceIndex));
            if (!$tokens[$openBraceIndex]->equals('{')) {
                continue;
            }

            $closeBraceIndex = $tokens->getNextNonWhitespace($openBraceIndex);
            \assert(\is_int($closeBraceIndex));
            if (!$tokens[$closeBraceIndex]->equals('}')) {
                continue;
            }

            $tokens->ensureWhitespaceAtIndex($openBraceIndex + 1, 0, '');

            $beforeOpenBraceIndex = $tokens->getPrevNonWhitespace($openBraceIndex);
            if (!$tokens[$beforeOpenBraceIndex]->isGivenKind([\T_COMMENT, \T_DOC_COMMENT])) {
                $tokens->ensureWhitespaceAtIndex($openBraceIndex - 1, 1, ' ');
            }
        }
    }
}
