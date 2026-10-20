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

namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Fixer for rules defined in PSR2 ¶4.6.
 *
 * @author Varga Bence <vbence@czentral.org>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoSpacesAfterFunctionNameFixer extends AbstractFixer
{
    /**
     * Token kinds which can work as function calls.
     */
    private const FUNCTIONY_TOKEN_KINDS = [
        \T_ARRAY,
        \T_ECHO,
        \T_EMPTY,
        \T_EVAL,
        \T_EXIT,
        \T_INCLUDE,
        \T_INCLUDE_ONCE,
        \T_ISSET,
        \T_LIST,
        \T_PRINT,
        \T_REQUIRE,
        \T_REQUIRE_ONCE,
        \T_UNSET,
        \T_VARIABLE,
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'When making a method or function call, there MUST NOT be a space between the method or function name and the opening parenthesis.',
            [new CodeSample("<?php\nstrlen ('Hello World!');\nfoo (test (3));\nexit  (1);\n\$func ();\n")],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before FunctionToConstantFixer, GetClassToClassKeywordFixer.
     * Must run after PowToExponentiationFixer.
     */
    public function getPriority(): int
    {
        return 3;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_STRING, ...self::FUNCTIONY_TOKEN_KINDS]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            // looking for start brace
            if (!$token->equals('(')) {
                continue;
            }

            // last non-whitespace token, can never be `null` always at least PHP open tag before it
            $lastTokenIndex = $tokens->getPrevNonWhitespace($index);

            // check for ternary operator
            $endParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index);
            $nextNonWhiteSpace = $tokens->getNextMeaningfulToken($endParenthesisIndex);
            if (
                null !== $nextNonWhiteSpace
                && !$tokens[$nextNonWhiteSpace]->equals(';')
                && $tokens[$lastTokenIndex]->isGivenKind([
                    \T_ECHO,
                    \T_PRINT,
                    \T_INCLUDE,
                    \T_INCLUDE_ONCE,
                    \T_REQUIRE,
                    \T_REQUIRE_ONCE,
                ])
            ) {
                continue;
            }

            // check if it is a function call
            if ($tokens[$lastTokenIndex]->isGivenKind(self::FUNCTIONY_TOKEN_KINDS)) {
                $this->fixFunctionCall($tokens, $index);
            } elseif ($tokens[$lastTokenIndex]->isGivenKind(\T_STRING)) { // for real function calls or definitions
                $possibleDefinitionIndex = $tokens->getPrevMeaningfulToken($lastTokenIndex);
                if (!$tokens[$possibleDefinitionIndex]->isGivenKind(\T_FUNCTION)) {
                    $this->fixFunctionCall($tokens, $index);
                }
            } elseif ($tokens[$lastTokenIndex]->equalsAny([
                ')',
                ']',
                [CT::T_DYNAMIC_VAR_BRACE_CLOSE],
                [CT::T_ARRAY_INDEX_BRACE_CLOSE],
            ])) {
                $block = Tokens::detectBlockType($tokens[$lastTokenIndex]);
                if (
                    Tokens::BLOCK_TYPE_INDEX_BRACE === $block['type']
                    || Tokens::BLOCK_TYPE_DYNAMIC_VAR_BRACE === $block['type']
                    || Tokens::BLOCK_TYPE_INDEX_BRACKET === $block['type']
                    || Tokens::BLOCK_TYPE_PARENTHESIS === $block['type']
                ) {
                    $this->fixFunctionCall($tokens, $index);
                }
            }
        }
    }

    /**
     * Fixes whitespaces around braces of a function(y) call.
     *
     * @param Tokens $tokens tokens to handle
     * @param int    $index  index of token
     */
    private function fixFunctionCall(Tokens $tokens, int $index): void
    {
        // remove space before opening brace
        if ($tokens[$index - 1]->isWhitespace()) {
            $tokens->clearAt($index - 1);
        }
    }
}
