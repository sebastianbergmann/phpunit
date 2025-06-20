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
final class NoLeadingSlashInGlobalNamespaceFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Classes in the global namespace cannot contain leading slashes.',
            [new CodeSample('<?php
$x = new \\Foo();
namespace Bar;
$y = new \\Baz();
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
        return $tokens->isTokenKindFound(\T_NS_SEPARATOR);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $index = 0;
        while (++$index < $tokens->count()) {
            $index = $this->skipNamespacedCode($tokens, $index);

            if (!$this->isToRemove($tokens, $index)) {
                continue;
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($index);
        }
    }

    private function isToRemove(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->isGivenKind(\T_NS_SEPARATOR)) {
            return false;
        }

        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        \assert(\is_int($prevIndex));

        if ($tokens[$prevIndex]->isGivenKind(\T_STRING)) {
            return false;
        }
        if ($tokens[$prevIndex]->isGivenKind([\T_NEW, CT::T_NULLABLE_TYPE, CT::T_TYPE_COLON])) {
            return true;
        }

        $nextIndex = $tokens->getTokenNotOfKindSibling($index, 1, [[\T_COMMENT], [\T_DOC_COMMENT], [\T_NS_SEPARATOR], [\T_STRING], [\T_WHITESPACE]]);
        \assert(\is_int($nextIndex));

        if ($tokens[$nextIndex]->isGivenKind(\T_DOUBLE_COLON)) {
            return true;
        }

        return $tokens[$prevIndex]->equalsAny(['(', ',', [CT::T_TYPE_ALTERNATION]]) && $tokens[$nextIndex]->isGivenKind([\T_VARIABLE, CT::T_TYPE_ALTERNATION]);
    }

    private function skipNamespacedCode(Tokens $tokens, int $index): int
    {
        if (!$tokens[$index]->isGivenKind(\T_NAMESPACE)) {
            return $index;
        }

        $nextIndex = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($nextIndex));

        if ($tokens[$nextIndex]->equals('{')) {
            return $nextIndex;
        }

        $nextIndex = $tokens->getNextTokenOfKind($index, ['{', ';']);
        \assert(\is_int($nextIndex));

        if ($tokens[$nextIndex]->equals(';')) {
            return $tokens->count() - 1;
        }

        return $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $nextIndex);
    }
}
