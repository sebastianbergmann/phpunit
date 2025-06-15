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
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\Analyzer\Analysis\ArrayElementAnalysis;
use PhpCsFixerCustomFixers\Analyzer\ArrayAnalyzer;
use PhpCsFixerCustomFixers\TokenRemover;

/**
 * @implements ConfigurableFixerInterface<_InputConfig, _Config>
 *
 * @phpstan-type _InputConfig array{ignore_expressions?: bool}
 * @phpstan-type _Config array{ignore_expressions: bool}
 *
 * @no-named-arguments
 */
final class NoDuplicatedArrayKeyFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    private bool $ignoreExpressions = true;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no duplicate array keys.',
            [new CodeSample('<?php
$x = [
    "foo" => 1,
    "bar" => 2,
    "foo" => 3,
];
')],
            '',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('ignore_expressions', 'whether to keep duplicated expressions (as they might return different values) or not'))
                ->setAllowedTypes(['bool'])
                ->setDefault($this->ignoreExpressions)
                ->getOption(),
        ]);
    }

    /**
     * @param array<string, bool> $configuration
     */
    public function configure(array $configuration): void
    {
        if (\array_key_exists('ignore_expressions', $configuration)) {
            $this->ignoreExpressions = $configuration['ignore_expressions'];
        }
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind([\T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN])) {
                continue;
            }

            $this->fixArray($tokens, $index);
        }
    }

    private function fixArray(Tokens $tokens, int $index): void
    {
        $arrayAnalyzer = new ArrayAnalyzer();

        $foundKeys = [];
        foreach (\array_reverse($arrayAnalyzer->getElements($tokens, $index)) as $arrayElementAnalysis) {
            $key = $this->getKeyContentIfPossible($tokens, $arrayElementAnalysis);
            if ($key === null) {
                continue;
            }
            if (\in_array($key, $foundKeys, true)) {
                $startIndex = $arrayElementAnalysis->getKeyStartIndex();
                \assert(\is_int($startIndex));

                $endIndex = $tokens->getNextMeaningfulToken($arrayElementAnalysis->getValueEndIndex());
                \assert(\is_int($endIndex));

                if ($tokens[$endIndex + 1]->isWhitespace() && Preg::match('/^\\h+$/', $tokens[$endIndex + 1]->getContent())) {
                    $endIndex++;
                }

                $tokens->clearRange($startIndex + 1, $endIndex);
                TokenRemover::removeWithLinesIfPossible($tokens, $startIndex);
            }
            $foundKeys[] = $key;
        }
    }

    private function getKeyContentIfPossible(Tokens $tokens, ArrayElementAnalysis $arrayElementAnalysis): ?string
    {
        if ($arrayElementAnalysis->getKeyStartIndex() === null) {
            return null;
        }

        $keyEndIndex = $arrayElementAnalysis->getKeyEndIndex();
        \assert(\is_int($keyEndIndex));

        $content = '';
        for ($index = $keyEndIndex; $index >= $arrayElementAnalysis->getKeyStartIndex(); $index--) {
            if ($tokens[$index]->isWhitespace() || $tokens[$index]->isComment()) {
                continue;
            }

            if ($this->ignoreExpressions && $tokens[$index]->equalsAny([[\T_VARIABLE], '('])) {
                return null;
            }

            $content .= $tokens[$index]->getContent();
        }

        return $content;
    }
}
