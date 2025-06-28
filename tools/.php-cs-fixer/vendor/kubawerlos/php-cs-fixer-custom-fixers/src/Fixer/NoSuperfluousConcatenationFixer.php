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

use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @implements ConfigurableFixerInterface<_InputConfig, _Config>
 *
 * @phpstan-type _InputConfig array{allow_preventing_trailing_spaces?: bool}
 * @phpstan-type _Config array{allow_preventing_trailing_spaces: bool}
 *
 * @no-named-arguments
 */
final class NoSuperfluousConcatenationFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    private bool $allowPreventingTrailingSpaces = false;
    private bool $keepConcatenationForDifferentQuotes = false;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no superfluous concatenation of literal strings.',
            [new CodeSample("<?php\necho 'foo' . 'bar';\n")],
            '',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('allow_preventing_trailing_spaces', 'whether to keep concatenation if it prevents having trailing spaces in string'))
                ->setAllowedTypes(['bool'])
                ->setDefault($this->allowPreventingTrailingSpaces)
                ->getOption(),
            (new FixerOptionBuilder('keep_concatenation_for_different_quotes', 'whether to keep concatenation if single-quoted and double-quoted would be concatenated'))
                ->setAllowedTypes(['bool'])
                ->setDefault($this->keepConcatenationForDifferentQuotes)
                ->getOption(),
        ]);
    }

    /**
     * @param array<string, bool> $configuration
     */
    public function configure(array $configuration): void
    {
        if (\array_key_exists('allow_preventing_trailing_spaces', $configuration)) {
            $this->allowPreventingTrailingSpaces = $configuration['allow_preventing_trailing_spaces'];
        }
        if (\array_key_exists('keep_concatenation_for_different_quotes', $configuration)) {
            $this->keepConcatenationForDifferentQuotes = $configuration['keep_concatenation_for_different_quotes'];
        }
    }

    /**
     * Must run after SingleLineThrowFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound(['.', \T_CONSTANT_ENCAPSED_STRING]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->equals('.')) {
                continue;
            }

            $firstIndex = $this->getFirstIndex($tokens, $index);
            if ($firstIndex === null) {
                continue;
            }

            $secondIndex = $this->getSecondIndex($tokens, $index);
            if ($secondIndex === null) {
                continue;
            }

            if (
                $this->keepConcatenationForDifferentQuotes
                && \substr($tokens[$firstIndex]->getContent(), 0, 1) !== \substr($tokens[$secondIndex]->getContent(), 0, 1)
            ) {
                continue;
            }

            $this->fixConcat($tokens, $firstIndex, $secondIndex);
        }
    }

    private function getFirstIndex(Tokens $tokens, int $index): ?int
    {
        $firstIndex = $tokens->getPrevMeaningfulToken($index);
        \assert(\is_int($firstIndex));

        if (!$tokens[$firstIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
            return null;
        }
        if (!$this->areOnlyHorizontalWhitespacesBetween($tokens, $firstIndex, $index)) {
            return null;
        }

        return $firstIndex;
    }

    private function getSecondIndex(Tokens $tokens, int $index): ?int
    {
        $secondIndex = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($secondIndex));

        if (!$tokens[$secondIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
            return null;
        }
        if (!$this->areOnlyHorizontalWhitespacesBetween($tokens, $index, $secondIndex)) {
            return null;
        }

        return $secondIndex;
    }

    private function areOnlyHorizontalWhitespacesBetween(Tokens $tokens, int $indexStart, int $indexEnd): bool
    {
        for ($index = $indexStart + 1; $index < $indexEnd; $index++) {
            if (!$tokens[$index]->isGivenKind(\T_WHITESPACE)) {
                return false;
            }
            if (Preg::match('/\\R/', $tokens[$index]->getContent())) {
                return false;
            }
        }

        return true;
    }

    private function fixConcat(Tokens $tokens, int $firstIndex, int $secondIndex): void
    {
        $prefix = '';
        $firstContent = $tokens[$firstIndex]->getContent();
        $secondContent = $tokens[$secondIndex]->getContent();

        if (
            $this->allowPreventingTrailingSpaces
            && Preg::match('/\\h(\\\'|")$/', $firstContent)
            && Preg::match('/^(\\\'|")\\R/', $secondContent)
        ) {
            return;
        }

        if (\strtolower($firstContent[0]) === 'b') {
            $prefix = $firstContent[0];
            $firstContent = \ltrim($firstContent, 'bB');
        }

        $secondContent = \ltrim($secondContent, 'bB');

        $border = $firstContent[0] === '"' || $secondContent[0] === '"' ? '"' : "'";

        $tokens->overrideRange(
            $firstIndex,
            $secondIndex,
            [
                new Token(
                    [\T_CONSTANT_ENCAPSED_STRING,
                        $prefix . $border . $this->getContentForBorder($firstContent, $border, true) . $this->getContentForBorder($secondContent, $border, false) . $border,
                    ],
                ),
            ],
        );
    }

    private function getContentForBorder(string $content, string $targetBorder, bool $escapeDollarWhenIsLastCharacter): string
    {
        $currentBorder = $content[0];
        $content = \substr($content, 1, -1);

        if ($content === '') {
            return '';
        }

        if ($currentBorder === '"') {
            if ($escapeDollarWhenIsLastCharacter && $content[\strlen($content) - 1] === '$') {
                $content = \substr($content, 0, -1) . '\\$';
            }

            return $content;
        }
        if ($targetBorder === "'") {
            return $content;
        }

        // unescape single quote
        $content = \str_replace('\\\'', '\'', $content);

        // escape dollar sign
        $content = \str_replace('$', '\\$', $content);

        // escape double quote
        return Preg::replace(
            '/(?<!\\\\)((\\\\{2})*)(\\\\)?"/',
            '$1\\\\$3$3"',
            $content,
        );
    }
}
