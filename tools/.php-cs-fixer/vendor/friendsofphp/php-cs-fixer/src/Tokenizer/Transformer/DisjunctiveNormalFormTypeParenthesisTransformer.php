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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Transform DNF parentheses into CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN and CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE.
 *
 * @see https://wiki.php.net/rfc/dnf_types
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DisjunctiveNormalFormTypeParenthesisTransformer extends AbstractTransformer
{
    public function getPriority(): int
    {
        // needs to run after TypeAlternationTransformer
        return -16;
    }

    public function getRequiredPhpVersionId(): int
    {
        return 8_02_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(CT::T_TYPE_ALTERNATION);
    }

    public function process(Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if ($token->equals('(') && $tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind(CT::T_TYPE_ALTERNATION)) {
                $openIndex = $index;
                $closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index);
            } elseif ($token->equals(')') && $tokens[$tokens->getNextMeaningfulToken($index)]->isGivenKind(CT::T_TYPE_ALTERNATION)) {
                $openIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $index);
                $closeIndex = $index;
            } else {
                continue;
            }

            $tokens[$openIndex] = new Token([CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN, '(']);
            $tokens[$closeIndex] = new Token([CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE, ')']);
        }
    }

    public function getCustomTokens(): array
    {
        return [
            CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN,
            CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE,
        ];
    }
}
