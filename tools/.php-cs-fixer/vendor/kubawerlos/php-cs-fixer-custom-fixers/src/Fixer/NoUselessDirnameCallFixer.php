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
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class NoUselessDirnameCallFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be no useless `dirname` calls.',
            [new CodeSample('<?php
require dirname(__DIR__) . "/vendor/autoload.php";
')],
            '',
        );
    }

    /**
     * Must run before ConcatSpaceFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DIR);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_DIR)) {
                continue;
            }

            $prevInserts = $this->getPrevTokensUpdates($tokens, $index);
            if ($prevInserts === null) {
                continue;
            }

            $nextInserts = $this->getNextTokensUpdates($tokens, $index);
            if ($nextInserts === null) {
                continue;
            }

            foreach ($prevInserts + $nextInserts as $i => $content) {
                if ($content === '') {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($i);
                } else {
                    $tokens[$i] = new Token([\T_CONSTANT_ENCAPSED_STRING, $content]);
                }
            }
        }
    }

    /**
     * @return null|array<int, string>
     */
    private function getPrevTokensUpdates(Tokens $tokens, int $index): ?array
    {
        $updates = [];

        $openParenthesisIndex = $tokens->getPrevMeaningfulToken($index);
        \assert(\is_int($openParenthesisIndex));
        if (!$tokens[$openParenthesisIndex]->equals('(')) {
            return null;
        }
        $updates[$openParenthesisIndex] = '';

        $dirnameCallIndex = $tokens->getPrevMeaningfulToken($openParenthesisIndex);
        \assert(\is_int($dirnameCallIndex));
        if (!$tokens[$dirnameCallIndex]->equals([\T_STRING, 'dirname'], false)) {
            return null;
        }
        if (!(new FunctionsAnalyzer())->isGlobalFunctionCall($tokens, $dirnameCallIndex)) {
            return null;
        }
        $updates[$dirnameCallIndex] = '';

        $namespaceSeparatorIndex = $tokens->getPrevMeaningfulToken($dirnameCallIndex);
        \assert(\is_int($namespaceSeparatorIndex));
        if ($tokens[$namespaceSeparatorIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $updates[$namespaceSeparatorIndex] = '';
        }

        return $updates;
    }

    /**
     * @return null|array<int, string>
     */
    private function getNextTokensUpdates(Tokens $tokens, int $index): ?array
    {
        $depthLevel = 1;
        $updates = [];

        $commaOrClosingParenthesisIndex = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($commaOrClosingParenthesisIndex));
        if ($tokens[$commaOrClosingParenthesisIndex]->equals(',')) {
            $updates[$commaOrClosingParenthesisIndex] = '';
            $afterCommaIndex = $tokens->getNextMeaningfulToken($commaOrClosingParenthesisIndex);
            \assert(\is_int($afterCommaIndex));
            if ($tokens[$afterCommaIndex]->isGivenKind(\T_LNUMBER)) {
                $depthLevel = (int) $tokens[$afterCommaIndex]->getContent();
                $updates[$afterCommaIndex] = '';
                $commaOrClosingParenthesisIndex = $tokens->getNextMeaningfulToken($afterCommaIndex);
                \assert(\is_int($commaOrClosingParenthesisIndex));
            }
        }

        if ($tokens[$commaOrClosingParenthesisIndex]->equals(',')) {
            $updates[$commaOrClosingParenthesisIndex] = '';
            $commaOrClosingParenthesisIndex = $tokens->getNextMeaningfulToken($commaOrClosingParenthesisIndex);
            \assert(\is_int($commaOrClosingParenthesisIndex));
        }
        $closingParenthesisIndex = $commaOrClosingParenthesisIndex;

        if (!$tokens[$closingParenthesisIndex]->equals(')')) {
            return null;
        }
        $updates[$closingParenthesisIndex] = '';

        $concatenationIndex = $tokens->getNextMeaningfulToken($closingParenthesisIndex);
        \assert(\is_int($concatenationIndex));
        if (!$tokens[$concatenationIndex]->equals('.')) {
            return null;
        }

        $stringIndex = $tokens->getNextMeaningfulToken($concatenationIndex);
        \assert(\is_int($stringIndex));
        if (!$tokens[$stringIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
            return null;
        }

        $stringContent = $tokens[$stringIndex]->getContent();
        $updates[$stringIndex] = \substr($stringContent, 0, 1) . \str_repeat('/..', $depthLevel) . \substr($stringContent, 1);

        return $updates;
    }
}
