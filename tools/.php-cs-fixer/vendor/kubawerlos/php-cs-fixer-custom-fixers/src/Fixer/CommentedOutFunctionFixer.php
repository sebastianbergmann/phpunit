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
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\Analyzer\SwitchAnalyzer;

/**
 * @implements ConfigurableFixerInterface<_InputConfig, _Config>
 *
 * @phpstan-type _InputConfig array{functions?: list<string>}
 * @phpstan-type _Config array{functions: list<string>}
 *
 * @no-named-arguments
 */
final class CommentedOutFunctionFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    /** @var list<string> */
    private array $functions = ['print_r', 'var_dump', 'var_export'];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'The configured functions must be commented out.',
            [new CodeSample('<?php
var_dump($x);
')],
            '',
            'when any of the configured functions have side effects or are overwritten',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('functions', 'list of functions to comment out'))
                ->setDefault($this->functions)
                ->setAllowedTypes(['array'])
                ->getOption(),
        ]);
    }

    /**
     * @param array<string, list<string>> $configuration
     */
    public function configure(array $configuration): void
    {
        if (\array_key_exists('functions', $configuration)) {
            $this->functions = $configuration['functions'];
        }
    }

    /**
     * Must run before CommentSurroundedBySpacesFixer, NoCommentedOutCodeFixer.
     */
    public function getPriority(): int
    {
        return 57;
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
            if (!$this->isFunctionToFix($tokens, $index)) {
                continue;
            }

            $startIndex = $index;

            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            \assert(\is_int($prevIndex));

            if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                $startIndex = $prevIndex;
            }

            if (!$this->isPreviousTokenSeparateStatement($tokens, $startIndex)) {
                continue;
            }

            $indexParenthesisStart = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($indexParenthesisStart));

            $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $indexParenthesisStart);

            $semicolonIndex = $tokens->getNextMeaningfulToken($endIndex);
            \assert(\is_int($semicolonIndex));

            if (!$tokens[$semicolonIndex]->equalsAny([';', [\T_CLOSE_TAG]])) {
                continue;
            }

            if ($tokens[$semicolonIndex]->equals(';')) {
                $endIndex = $semicolonIndex;
            }

            $this->fixBlock($tokens, $startIndex, $endIndex);
        }
    }

    private function isFunctionToFix(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->isGivenKind(\T_STRING)) {
            return false;
        }

        if (!\in_array(\strtolower($tokens[$index]->getContent()), $this->functions, true)) {
            return false;
        }

        return (new FunctionsAnalyzer())->isGlobalFunctionCall($tokens, $index);
    }

    private function isPreviousTokenSeparateStatement(Tokens $tokens, int $index): bool
    {
        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        \assert(\is_int($prevIndex));

        if ($tokens[$prevIndex]->equalsAny([';', '{', '}', [\T_OPEN_TAG]])) {
            return true;
        }

        $switchAnalyzer = new SwitchAnalyzer();

        if (!$tokens[$prevIndex]->equals(':')) { // can be part of ternary operator or from switch/case
            return false;
        }

        for ($i = $index; $i > 0; $i--) {
            if (!$tokens[$i]->isGivenKind(\T_SWITCH)) {
                continue;
            }
            foreach ($switchAnalyzer->getSwitchAnalysis($tokens, $i)->getCases() as $caseAnalysis) {
                if ($caseAnalysis->getColonIndex() === $prevIndex) {
                    return true;
                }
            }
        }

        return false;
    }

    private function fixBlock(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        if ($this->canUseSingleLineComment($tokens, $startIndex, $endIndex)) {
            $this->fixBlockWithSingleLineComments($tokens, $startIndex, $endIndex);

            return;
        }

        $tokens->overrideRange(
            $startIndex,
            $endIndex,
            [new Token([\T_COMMENT, '/*' . $tokens->generatePartialCode($startIndex, $endIndex) . '*/'])],
        );
    }

    private function canUseSingleLineComment(Tokens $tokens, int $startIndex, int $endIndex): bool
    {
        if (!$tokens->offsetExists($endIndex + 1)) {
            return true;
        }

        if (Preg::match('/^\\R/', $tokens[$endIndex + 1]->getContent())) {
            return true;
        }

        for ($index = $startIndex; $index < $endIndex; $index++) {
            if (\strpos($tokens[$index]->getContent(), '*/') !== false) {
                return true;
            }
        }

        return false;
    }

    private function fixBlockWithSingleLineComments(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        $codeToCommentOut = $tokens->generatePartialCode($startIndex, $endIndex);

        $prefix = '//';
        if ($tokens[$startIndex - 1]->isWhitespace()) {
            $startIndex--;
            $prefix = Preg::replace('/(^|\\R)(\\h*$)/D', '$1//$2', $tokens[$startIndex]->getContent());
        }
        $codeToCommentOut = $prefix . \str_replace("\n", "\n//", $codeToCommentOut);

        if ($tokens->offsetExists($endIndex + 1)) {
            if (!Preg::match('/^\\R/', $tokens[$endIndex + 1]->getContent())) {
                $codeToCommentOut .= "\n";
                if ($tokens[$endIndex + 1]->isWhitespace()) {
                    $endIndex++;
                    $codeToCommentOut .= $tokens[$endIndex]->getContent();
                }
            }
        }

        $newTokens = Tokens::fromCode('<?php ' . $codeToCommentOut);
        $newTokens->clearAt(0);
        $newTokens->clearEmptyTokens();

        $tokens->overrideRange(
            $startIndex,
            $endIndex,
            $newTokens,
        );
    }
}
