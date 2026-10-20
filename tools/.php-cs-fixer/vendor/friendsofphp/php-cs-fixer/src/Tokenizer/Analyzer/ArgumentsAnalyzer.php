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

namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Analyzer\Analysis\ArgumentAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\TypeAnalysis;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Vladimir Reznichenko <kalessil@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ArgumentsAnalyzer
{
    private const ARGUMENT_INFO_SKIP_TYPES = [\T_ELLIPSIS, \T_FINAL, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE, FCT::T_READONLY, FCT::T_PRIVATE_SET, FCT::T_PROTECTED_SET, FCT::T_PUBLIC_SET];

    /**
     * Count amount of parameters in a function/method reference.
     */
    public function countArguments(Tokens $tokens, int $openParenthesis, int $closeParenthesis): int
    {
        return \count($this->getArguments($tokens, $openParenthesis, $closeParenthesis));
    }

    /**
     * Returns start and end token indices of arguments.
     *
     * Returns an array with each key being the first token of an
     * argument and the value the last. Including non-function tokens
     * such as comments and white space tokens, but without the separation
     * tokens like '(', ',' and ')'.
     *
     * @return array<int, int>
     */
    public function getArguments(Tokens $tokens, int $openParenthesis, int $closeParenthesis): array
    {
        $arguments = [];
        $firstSensibleToken = $tokens->getNextMeaningfulToken($openParenthesis);

        if ($tokens[$firstSensibleToken]->equals(')')) {
            return $arguments;
        }

        $paramContentIndex = $openParenthesis + 1;
        $argumentsStart = $paramContentIndex;

        for (; $paramContentIndex < $closeParenthesis; ++$paramContentIndex) {
            $token = $tokens[$paramContentIndex];

            // skip nested (), [], {} constructs
            $blockDefinitionProbe = Tokens::detectBlockType($token);

            if (null !== $blockDefinitionProbe && true === $blockDefinitionProbe['isStart']) {
                $paramContentIndex = $tokens->findBlockEnd($blockDefinitionProbe['type'], $paramContentIndex);

                continue;
            }

            // if comma matched, increase arguments counter
            if ($token->equals(',')) {
                if ($tokens->getNextMeaningfulToken($paramContentIndex) === $closeParenthesis) {
                    break; // trailing ',' in function call (PHP 7.3)
                }

                $arguments[$argumentsStart] = $paramContentIndex - 1;
                $argumentsStart = $paramContentIndex + 1;
            }
        }

        $arguments[$argumentsStart] = $paramContentIndex - 1;

        return $arguments;
    }

    public function getArgumentInfo(Tokens $tokens, int $argumentStart, int $argumentEnd): ArgumentAnalysis
    {
        $info = [
            'default' => null,
            'name' => null,
            'name_index' => null,
            'type' => null,
            'type_index_start' => null,
            'type_index_end' => null,
        ];

        $sawName = false;

        for ($index = $argumentStart; $index <= $argumentEnd; ++$index) {
            $token = $tokens[$index];

            if ($token->isGivenKind(FCT::T_ATTRIBUTE)) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);

                continue;
            }

            if ($token->isGivenKind(CT::T_PROPERTY_HOOK_BRACE_OPEN)) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PROPERTY_HOOK, $index);

                continue;
            }

            if (
                $token->isComment()
                || $token->isWhitespace()
                || $token->isGivenKind(self::ARGUMENT_INFO_SKIP_TYPES)
                || $token->equals('&')
            ) {
                continue;
            }

            if ($token->isGivenKind(\T_VARIABLE)) {
                if ($sawName) {
                    continue;
                }
                $sawName = true;
                $info['name_index'] = $index;
                $info['name'] = $token->getContent();

                continue;
            }

            if ($token->equals('=')) {
                continue;
            }

            if ($sawName) {
                $info['default'] .= $token->getContent();
            } else {
                $info['type_index_start'] = ($info['type_index_start'] > 0) ? $info['type_index_start'] : $index;
                $info['type_index_end'] = $index;
                $info['type'] .= $token->getContent();
            }
        }

        if (null === $info['name']) {
            $info['type'] = null;
        }

        return new ArgumentAnalysis(
            $info['name'],
            $info['name_index'],
            $info['default'],
            null !== $info['type'] ? new TypeAnalysis($info['type'], $info['type_index_start'], $info['type_index_end']) : null,
        );
    }
}
