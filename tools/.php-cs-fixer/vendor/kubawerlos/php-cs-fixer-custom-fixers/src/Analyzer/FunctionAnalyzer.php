<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Analyzer;

use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\Analyzer\Analysis\ArgumentAnalysis;

/**
 * @internal
 */
final class FunctionAnalyzer
{
    /**
     * @return list<ArgumentAnalysis>
     */
    public static function getFunctionArguments(Tokens $tokens, int $index): array
    {
        $argumentsRange = self::getArgumentsRange($tokens, $index);
        if ($argumentsRange === null) {
            return [];
        }

        [$argumentStartIndex, $argumentsEndIndex] = $argumentsRange;

        $arguments = [];
        $index = $currentArgumentStart = $argumentStartIndex;
        while ($index < $argumentsEndIndex) {
            $blockType = Tokens::detectBlockType($tokens[$index]);
            if ($blockType !== null && $blockType['isStart']) {
                $index = $tokens->findBlockEnd($blockType['type'], $index);
                continue;
            }

            $index = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($index));

            if (!$tokens[$index]->equals(',')) {
                continue;
            }

            $currentArgumentEnd = $tokens->getPrevMeaningfulToken($index);
            \assert(\is_int($currentArgumentEnd));

            $arguments[] = self::createArgumentAnalysis($tokens, $currentArgumentStart, $currentArgumentEnd);

            $currentArgumentStart = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($currentArgumentStart));
        }

        $arguments[] = self::createArgumentAnalysis($tokens, $currentArgumentStart, $argumentsEndIndex);

        return $arguments;
    }

    /**
     * @return null|array{int, int}
     */
    private static function getArgumentsRange(Tokens $tokens, int $index): ?array
    {
        if (!$tokens[$index]->isGivenKind([\T_ISSET, \T_STRING])) {
            throw new \InvalidArgumentException(\sprintf('Index %d is not a function.', $index));
        }

        $openParenthesis = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($openParenthesis));
        if (!$tokens[$openParenthesis]->equals('(')) {
            throw new \InvalidArgumentException(\sprintf('Index %d is not a function.', $index));
        }

        $closeParenthesis = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesis);

        $argumentsEndIndex = $tokens->getPrevMeaningfulToken($closeParenthesis);
        \assert(\is_int($argumentsEndIndex));

        if ($openParenthesis === $argumentsEndIndex) {
            return null;
        }
        if ($tokens[$argumentsEndIndex]->equals(',')) {
            $argumentsEndIndex = $tokens->getPrevMeaningfulToken($argumentsEndIndex);
            \assert(\is_int($argumentsEndIndex));
        }

        $argumentStartIndex = $tokens->getNextMeaningfulToken($openParenthesis);
        \assert(\is_int($argumentStartIndex));

        return [$argumentStartIndex, $argumentsEndIndex];
    }

    private static function createArgumentAnalysis(Tokens $tokens, int $startIndex, int $endIndex): ArgumentAnalysis
    {
        $isConstant = true;

        for ($index = $startIndex; $index <= $endIndex; $index++) {
            if ($tokens[$index]->isGivenKind(\T_VARIABLE)) {
                $isConstant = false;
            }
            if ($tokens[$index]->equals('(')) {
                $prevParenthesisIndex = $tokens->getPrevMeaningfulToken($index);
                \assert(\is_int($prevParenthesisIndex));

                if (!$tokens[$prevParenthesisIndex]->isGivenKind(\T_ARRAY)) {
                    $isConstant = false;
                }
            }
        }

        return new ArgumentAnalysis($startIndex, $endIndex, $isConstant);
    }
}
