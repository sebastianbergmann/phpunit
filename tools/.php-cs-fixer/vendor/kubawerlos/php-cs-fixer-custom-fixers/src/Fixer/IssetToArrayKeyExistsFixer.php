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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\Analyzer\FunctionAnalyzer;

/**
 * @no-named-arguments
 */
final class IssetToArrayKeyExistsFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Function `array_key_exists` must be used instead of `isset` when possible.',
            [
                new CodeSample(
                    '<?php
if (isset($array[$key])) {
    echo $array[$key];
}
',
                ),
            ],
            '',
            'when array is not defined, is multi-dimensional or behaviour is relying on the null value',
        );
    }

    /**
     * Must run before NativeFunctionInvocationFixer.
     */
    public function getPriority(): int
    {
        return 2;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_ISSET);
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_ISSET)) {
                continue;
            }

            if (\count(FunctionAnalyzer::getFunctionArguments($tokens, $index)) !== 1) {
                continue;
            }

            $openParenthesis = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($openParenthesis));

            $closeParenthesis = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesis);

            $closeBrackets = $tokens->getPrevMeaningfulToken($closeParenthesis);
            \assert(\is_int($closeBrackets));
            if (!$tokens[$closeBrackets]->equals(']')) {
                continue;
            }

            $openBrackets = $tokens->findBlockStart(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $closeBrackets);

            $keyStartIndex = $tokens->getNextMeaningfulToken($openBrackets);
            \assert(\is_int($keyStartIndex));
            $keyEndIndex = $tokens->getPrevMeaningfulToken($closeBrackets);

            $keyTokens = [];
            for ($i = $keyStartIndex; $i <= $keyEndIndex; $i++) {
                if ($tokens[$i]->equals('')) {
                    continue;
                }
                $keyTokens[] = $tokens[$i];
            }
            $keyTokens[] = new Token(',');
            $keyTokens[] = new Token([\T_WHITESPACE, ' ']);

            $tokens->clearRange($openBrackets, $closeBrackets);
            $tokens->insertAt($openParenthesis + 1, $keyTokens);
            $tokens[$index] = new Token([\T_STRING, 'array_key_exists']);
        }
    }
}
