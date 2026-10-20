<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tokenizer\Transformer;

use PhpCsFixer\Tokenizer\AbstractTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Transform discriminate overloaded curly braces tokens.
 *
 * Performed transformations:
 * - closing `}` for T_CURLY_OPEN into CT::T_CURLY_CLOSE,
 * - closing `}` for T_DOLLAR_OPEN_CURLY_BRACES into CT::T_DOLLAR_CLOSE_CURLY_BRACES,
 * - in `$foo->{$bar}` into CT::T_DYNAMIC_PROP_BRACE_OPEN and CT::T_DYNAMIC_PROP_BRACE_CLOSE,
 * - in `${$foo}` into CT::T_DYNAMIC_VAR_BRACE_OPEN and CT::T_DYNAMIC_VAR_BRACE_CLOSE,
 * - in `$array{$index}` into CT::T_ARRAY_INDEX_BRACE_OPEN and CT::T_ARRAY_INDEX_BRACE_CLOSE,
 * - in `use some\a\{ClassA, ClassB, ClassC as C}` into CT::T_GROUP_IMPORT_BRACE_OPEN, CT::T_GROUP_IMPORT_BRACE_CLOSE,
 * - in `class PropertyHooks { public string $bar _{_ set(string $value) { } _}_` into CT::T_PROPERTY_HOOK_BRACE_OPEN, CT::T_PROPERTY_HOOK_BRACE_CLOSE.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class BraceTransformer extends AbstractTransformer
{
    public function getRequiredPhpVersionId(): int
    {
        return 5_00_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_CURLY_OPEN, \T_DOLLAR_OPEN_CURLY_BRACES, '{']);
    }

    public function processToken(Tokens $tokens, Token $token, int $index): void
    {
        $this->transformIntoCurlyCloseBrace($tokens, $index);
        $this->transformIntoDollarCloseBrace($tokens, $index);
        $this->transformIntoDynamicPropBraces($tokens, $index);
        $this->transformIntoDynamicVarBraces($tokens, $index);
        $this->transformIntoPropertyHookBraces($tokens, $index);
        $this->transformIntoCurlyIndexBraces($tokens, $index);
        $this->transformIntoGroupUseBraces($tokens, $index);
        $this->transformIntoDynamicClassConstantFetchBraces($tokens, $index);
    }

    public function getCustomTokens(): array
    {
        return [
            CT::T_CURLY_CLOSE,
            CT::T_DOLLAR_CLOSE_CURLY_BRACES,
            CT::T_DYNAMIC_PROP_BRACE_OPEN,
            CT::T_DYNAMIC_PROP_BRACE_CLOSE,
            CT::T_DYNAMIC_VAR_BRACE_OPEN,
            CT::T_DYNAMIC_VAR_BRACE_CLOSE,
            CT::T_ARRAY_INDEX_BRACE_OPEN,
            CT::T_ARRAY_INDEX_BRACE_CLOSE,
            CT::T_GROUP_IMPORT_BRACE_OPEN,
            CT::T_GROUP_IMPORT_BRACE_CLOSE,
            CT::T_DYNAMIC_CLASS_CONSTANT_FETCH_BRACE_OPEN,
            CT::T_DYNAMIC_CLASS_CONSTANT_FETCH_BRACE_CLOSE,
            CT::T_PROPERTY_HOOK_BRACE_OPEN,
            CT::T_PROPERTY_HOOK_BRACE_CLOSE,
        ];
    }

    /**
     * Transform closing `}` for T_CURLY_OPEN into CT::T_CURLY_CLOSE.
     *
     * This should be done at very beginning of curly braces transformations.
     */
    private function transformIntoCurlyCloseBrace(Tokens $tokens, int $index): void
    {
        $token = $tokens[$index];

        if (!$token->isGivenKind(\T_CURLY_OPEN)) {
            return;
        }

        $level = 1;

        do {
            ++$index;

            if ($tokens[$index]->equals('{') || $tokens[$index]->isGivenKind(\T_CURLY_OPEN)) { // we count all kind of {
                ++$level;
            } elseif ($tokens[$index]->equals('}')) { // we count all kind of }
                --$level;
            }
        } while (0 < $level);

        $tokens[$index] = new Token([CT::T_CURLY_CLOSE, '}']);
    }

    private function transformIntoDollarCloseBrace(Tokens $tokens, int $index): void
    {
        $token = $tokens[$index];

        if ($token->isGivenKind(\T_DOLLAR_OPEN_CURLY_BRACES)) {
            $nextIndex = $tokens->getNextTokenOfKind($index, ['}']);
            $tokens[$nextIndex] = new Token([CT::T_DOLLAR_CLOSE_CURLY_BRACES, '}']);
        }
    }

    private function transformIntoDynamicPropBraces(Tokens $tokens, int $index): void
    {
        $token = $tokens[$index];

        if (!$token->isObjectOperator()) {
            return;
        }

        if (!$tokens[$index + 1]->equals('{')) {
            return;
        }

        $openIndex = $index + 1;
        $closeIndex = $this->naivelyFindCurlyBlockEnd($tokens, $openIndex);

        $tokens[$openIndex] = new Token([CT::T_DYNAMIC_PROP_BRACE_OPEN, '{']);
        $tokens[$closeIndex] = new Token([CT::T_DYNAMIC_PROP_BRACE_CLOSE, '}']);
    }

    private function transformIntoDynamicVarBraces(Tokens $tokens, int $index): void
    {
        $token = $tokens[$index];

        if (!$token->equals('$')) {
            return;
        }

        $openIndex = $tokens->getNextMeaningfulToken($index);

        if (null === $openIndex) {
            return;
        }

        $openToken = $tokens[$openIndex];

        if (!$openToken->equals('{')) {
            return;
        }

        $closeIndex = $this->naivelyFindCurlyBlockEnd($tokens, $openIndex);

        $tokens[$openIndex] = new Token([CT::T_DYNAMIC_VAR_BRACE_OPEN, '{']);
        $tokens[$closeIndex] = new Token([CT::T_DYNAMIC_VAR_BRACE_CLOSE, '}']);
    }

    private function transformIntoPropertyHookBraces(Tokens $tokens, int $index): void
    {
        if (\PHP_VERSION_ID < 8_04_00) {
            return; // @TODO: drop condition when PHP 8.4+ is required or majority of the users are using 8.4+
        }

        $token = $tokens[$index];

        if (!$token->equals('{')) {
            return;
        }

        $nextIndex = $tokens->getNextMeaningfulToken($index);

        // skip attributes
        while ($tokens[$nextIndex]->isGivenKind(FCT::T_ATTRIBUTE)) {
            $nextIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ATTRIBUTE, $nextIndex);
            $nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
        }

        if (!$tokens[$nextIndex]->equalsAny([
            [\T_STRING, 'get'],
            [\T_STRING, 'set'],
        ], false)) {
            return;
        }

        $nextNextIndex = $tokens->getNextMeaningfulToken($nextIndex);

        if (!$tokens[$nextNextIndex]->equalsAny(['(', '{', ';', [\T_DOUBLE_ARROW]])) {
            return;
        }

        if ($tokens[$nextNextIndex]->equals('(')) {
            $closeParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $nextNextIndex);
            $afterCloseParenthesisIndex = $tokens->getNextMeaningfulToken($closeParenthesisIndex);
            if (!$tokens[$afterCloseParenthesisIndex]->equalsAny(['{', [\T_DOUBLE_ARROW]])) {
                return;
            }
        }

        $closeIndex = $this->naivelyFindCurlyBlockEnd($tokens, $index);

        $tokens[$index] = new Token([CT::T_PROPERTY_HOOK_BRACE_OPEN, '{']);
        $tokens[$closeIndex] = new Token([CT::T_PROPERTY_HOOK_BRACE_CLOSE, '}']);
    }

    private function transformIntoCurlyIndexBraces(Tokens $tokens, int $index): void
    {
        // Support for fetching array index with braces syntax (`$arr{$index}`)
        // was deprecated in 7.4 and removed in 8.0. However, the PHP's behaviour
        // differs between 8.0-8.3 (fatal error in runtime) and 8.4 (parse error).
        //
        // @TODO Do not replace `CT::T_ARRAY_INDEX_CURLY_BRACE_*` for 8.0-8.3, as further optimization
        if (\PHP_VERSION_ID >= 8_04_00) {
            return;
        }

        $token = $tokens[$index];

        if (!$token->equals('{')) {
            return;
        }

        $prevIndex = $tokens->getPrevMeaningfulToken($index);

        if (!$tokens[$prevIndex]->equalsAny([
            [\T_STRING],
            [\T_VARIABLE],
            [CT::T_ARRAY_INDEX_BRACE_CLOSE],
            ']',
            ')',
        ])) {
            return;
        }

        if (
            $tokens[$prevIndex]->isGivenKind(\T_STRING)
            && !$tokens[$tokens->getPrevMeaningfulToken($prevIndex)]->isObjectOperator()
        ) {
            return;
        }

        if (
            $tokens[$prevIndex]->equals(')')
            && !$tokens[$tokens->getPrevMeaningfulToken(
                $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $prevIndex),
            )]->isGivenKind(\T_ARRAY)
        ) {
            return;
        }

        $closeIndex = $this->naivelyFindCurlyBlockEnd($tokens, $index);

        $tokens[$index] = new Token([CT::T_ARRAY_INDEX_BRACE_OPEN, '{']);
        $tokens[$closeIndex] = new Token([CT::T_ARRAY_INDEX_BRACE_CLOSE, '}']);
    }

    private function transformIntoGroupUseBraces(Tokens $tokens, int $index): void
    {
        $token = $tokens[$index];

        if (!$token->equals('{')) {
            return;
        }

        $prevIndex = $tokens->getPrevMeaningfulToken($index);

        if (!$tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            return;
        }

        $closeIndex = $this->naivelyFindCurlyBlockEnd($tokens, $index);

        $tokens[$index] = new Token([CT::T_GROUP_IMPORT_BRACE_OPEN, '{']);
        $tokens[$closeIndex] = new Token([CT::T_GROUP_IMPORT_BRACE_CLOSE, '}']);
    }

    private function transformIntoDynamicClassConstantFetchBraces(Tokens $tokens, int $index): void
    {
        if (\PHP_VERSION_ID < 8_03_00) {
            return; // @TODO: drop condition when PHP 8.3+ is required or majority of the users are using 8.3+
        }

        $token = $tokens[$index];

        if (!$token->equals('{')) {
            return;
        }

        $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($index);

        while (!$tokens[$prevMeaningfulTokenIndex]->isGivenKind(\T_DOUBLE_COLON)) {
            if (!$tokens[$prevMeaningfulTokenIndex]->equals(')')) {
                return;
            }

            $prevMeaningfulTokenIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $prevMeaningfulTokenIndex);
            $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($prevMeaningfulTokenIndex);

            if (!$tokens[$prevMeaningfulTokenIndex]->equals('}')) {
                return;
            }

            $prevMeaningfulTokenIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_BRACE, $prevMeaningfulTokenIndex);
            $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($prevMeaningfulTokenIndex);
        }

        $closeIndex = $this->naivelyFindCurlyBlockEnd($tokens, $index);
        $nextMeaningfulTokenIndexAfterCloseIndex = $tokens->getNextMeaningfulToken($closeIndex);

        if (!$tokens[$nextMeaningfulTokenIndexAfterCloseIndex]->equalsAny([';', [\T_CLOSE_TAG], [\T_DOUBLE_COLON]])) {
            return;
        }

        $tokens[$index] = new Token([CT::T_DYNAMIC_CLASS_CONSTANT_FETCH_BRACE_OPEN, '{']);
        $tokens[$closeIndex] = new Token([CT::T_DYNAMIC_CLASS_CONSTANT_FETCH_BRACE_CLOSE, '}']);
    }

    /**
     * We do not want to rely on `$tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $index)` here,
     * as it relies on block types that are assuming that `}` tokens are already transformed to Custom Tokens that are allowing to distinguish different block types.
     * As we are just about to transform `{` and `}` into Custom Tokens by this transformer, thus we need to compare those tokens manually by content without using `Tokens::findBlockEnd`.
     */
    private function naivelyFindCurlyBlockEnd(Tokens $tokens, int $startIndex): int
    {
        if (!$tokens->offsetExists($startIndex)) {
            throw new \OutOfBoundsException(\sprintf('Unavailable index: "%s".', $startIndex));
        }

        if ('{' !== $tokens[$startIndex]->getContent()) {
            throw new \InvalidArgumentException(\sprintf('Wrong start index: "%s".', $startIndex));
        }

        $blockLevel = 1;
        $endIndex = $tokens->count() - 1;
        for ($index = $startIndex + 1; $index !== $endIndex; ++$index) {
            $token = $tokens[$index];

            if ('{' === $token->getContent()) {
                ++$blockLevel;

                continue;
            }

            if ('}' === $token->getContent()) {
                --$blockLevel;

                if (0 === $blockLevel) {
                    if (!$token->equals('}')) {
                        throw new \UnexpectedValueException(\sprintf('Detected block end for index: "%s" was already transformed into other token type: "%s".', $startIndex, $token->getName()));
                    }

                    return $index;
                }
            }
        }

        throw new \UnexpectedValueException(\sprintf('Missing block end for index: "%s".', $startIndex));
    }
}
