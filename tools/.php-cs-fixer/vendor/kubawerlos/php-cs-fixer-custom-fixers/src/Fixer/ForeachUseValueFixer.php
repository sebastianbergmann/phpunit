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
use PhpCsFixer\Tokenizer\Analyzer\AlternativeSyntaxAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class ForeachUseValueFixer extends AbstractFixer
{
    private const NOT_ALLOWED_NEXT_TOKENS = [
        '[',
        '=',
        [\T_INC, '++'],
        [\T_DEC, '--'],
        // arithmetic assignments
        [\T_PLUS_EQUAL, '+='],
        [\T_MINUS_EQUAL, '-='],
        [\T_MUL_EQUAL, '*='],
        [\T_DIV_EQUAL, '/='],
        [\T_MOD_EQUAL, '%='],
        [\T_POW_EQUAL, '**='],
        // bitwise assignments
        [\T_AND_EQUAL, '&='],
        [\T_OR_EQUAL, '|='],
        [\T_XOR_EQUAL, '^='],
        [\T_SL_EQUAL, '<<='],
        [\T_SR_EQUAL, '>>='],
        // other assignments
        [\T_COALESCE_EQUAL, '??='],
        [\T_CONCAT_EQUAL, '.='],
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Value from `foreach` must not be used if possible.',
            [new CodeSample(
                <<<'PHP'
                    <?php
                    foreach ($elements as $key => $value) {
                        $product *= $elements[$key];
                    }

                    PHP,
            )],
            '',
            'when the value is re-used or being sorted',
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_FOREACH, \T_VARIABLE]);
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_FOREACH)) {
                continue;
            }

            $openParenthesisIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($openParenthesisIndex));

            $closeParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesisIndex);

            $variables = $this->getForeachVariableNames($tokens, $openParenthesisIndex);
            if ($variables === null) {
                continue;
            }

            $blockStartIndex = $tokens->getNextMeaningfulToken($closeParenthesisIndex);
            \assert(\is_int($blockStartIndex));

            if ($tokens[$blockStartIndex]->equals('{')) {
                $blockEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $blockStartIndex);
            } elseif ($tokens[$blockStartIndex]->equals(':')) {
                $blockEndIndex = (new AlternativeSyntaxAnalyzer())->findAlternativeSyntaxBlockEnd($tokens, $index);
            } else {
                continue;
            }

            $this->fixForeachBody($tokens, $blockStartIndex, $blockEndIndex, ...$variables);
        }
    }

    /**
     * @return null|array{Token, string, string}
     */
    private function getForeachVariableNames(Tokens $tokens, int $openParenthesisIndex): ?array
    {
        $arrayIndex = $tokens->getNextMeaningfulToken($openParenthesisIndex);
        \assert(\is_int($arrayIndex));

        $asIndex = $tokens->getNextMeaningfulToken($arrayIndex);
        \assert(\is_int($asIndex));
        if (!$tokens[$asIndex]->isGivenKind(\T_AS)) {
            return null;
        }

        $keyIndex = $tokens->getNextMeaningfulToken($asIndex);
        \assert(\is_int($keyIndex));
        if (!$tokens[$keyIndex]->isGivenKind(\T_VARIABLE)) {
            return null;
        }

        $doubleArrayIndex = $tokens->getNextMeaningfulToken($keyIndex);
        \assert(\is_int($doubleArrayIndex));
        if (!$tokens[$doubleArrayIndex]->isGivenKind(\T_DOUBLE_ARROW)) {
            return null;
        }

        $variableIndex = $tokens->getNextMeaningfulToken($doubleArrayIndex);
        \assert(\is_int($variableIndex));
        if (!$tokens[$variableIndex]->isGivenKind(\T_VARIABLE)) {
            return null;
        }

        return [
            $tokens[$arrayIndex],
            $tokens[$keyIndex]->getContent(),
            $tokens[$variableIndex]->getContent(),
        ];
    }

    private function fixForeachBody(
        Tokens $tokens,
        int $openBraceIndex,
        int $closeBraceIndex,
        Token $arrayToken,
        string $keyName,
        string $variableName
    ): void {
        $sequence = [
            $arrayToken, '[', [\T_VARIABLE, $keyName], ']'];

        $index = $openBraceIndex;
        while (($found = $tokens->findSequence($sequence, $index, $closeBraceIndex)) !== null) {
            $startIndex = \array_key_first($found);
            $endIndex = \array_key_last($found);

            $index = $endIndex;

            if ($this->isInUnset($tokens, $startIndex)) {
                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($endIndex);
            \assert(\is_int($nextIndex));
            if ($tokens[$nextIndex]->equalsAny(self::NOT_ALLOWED_NEXT_TOKENS)) {
                continue;
            }

            $tokens->overrideRange($startIndex, $endIndex, [new Token([\T_VARIABLE, $variableName])]);
        }
    }

    private function isInUnset(Tokens $tokens, int $startIndex): bool
    {
        $openParenthesisIndex = $tokens->getPrevMeaningfulToken($startIndex);
        \assert(\is_int($openParenthesisIndex));
        if (!$tokens[$openParenthesisIndex]->equals('(')) {
            return false;
        }

        $unsetIndex = $tokens->getPrevMeaningfulToken($openParenthesisIndex);
        \assert(\is_int($unsetIndex));

        return $tokens[$unsetIndex]->isGivenKind(\T_UNSET);
    }
}
