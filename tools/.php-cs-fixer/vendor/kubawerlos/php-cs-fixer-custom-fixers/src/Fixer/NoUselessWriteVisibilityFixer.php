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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class NoUselessWriteVisibilityFixer extends AbstractFixer
{
    /** @var non-empty-array<int, list<int>> */
    private array $predecessorKindMap;

    public function __construct()
    {
        if (\defined('T_PUBLIC_SET')) {
            $this->predecessorKindMap = [
                \T_PUBLIC_SET => [\T_PUBLIC, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC],
                \T_PROTECTED_SET => [\T_PROTECTED, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED],
                \T_PRIVATE_SET => [\T_PRIVATE, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE],
            ];
        }
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no useless write visibility.',
            [new CodeSample(
                <<<'PHP'
                    <?php class Foo {
                        public public(set) $x;
                        public(set) $y;
                        protected protected(set) $z;
                    }

                    PHP,
            )],
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return \defined('T_PUBLIC_SET') && $tokens->isAnyTokenKindsFound(\array_keys($this->predecessorKindMap));
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens->findGivenKind(\array_keys($this->predecessorKindMap)) as $kind => $elements) {
            foreach (\array_keys($elements) as $index) {
                $this->fixVisibility($tokens, $index, $kind, $kind === \T_PUBLIC_SET);
            }
        }
    }

    private function fixVisibility(Tokens $tokens, int $index, int $kind, bool $makePublicIfNone): void
    {
        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        \assert(\is_int($prevIndex));
        if ($tokens[$prevIndex]->isGivenKind(\T_ABSTRACT)) {
            $prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
            \assert(\is_int($prevIndex));
        }

        if (!$tokens[$prevIndex]->isGivenKind($this->predecessorKindMap[$kind])) {
            if ($makePublicIfNone) {
                $prevDeciderIndex = $tokens->getPrevTokenOfKind($index, ['(', ';', '{']);
                \assert(\is_int($prevDeciderIndex));
                $kind = $tokens[$prevDeciderIndex]->equals('(') ? CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC : \T_PUBLIC;
                $tokens[$index] = new Token([$kind, 'public']);
            }

            return;
        }

        $tokens->clearAt($index);

        if ($tokens[$index + 1]->isWhitespace()) {
            $tokens->clearAt($index + 1);
        }
    }
}
