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
 * Transform `:` operator into CT::T_TYPE_COLON in `function foo() : int {}`.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TypeColonTransformer extends AbstractTransformer
{
    public function getPriority(): int
    {
        // needs to run after ReturnRefTransformer and UseTransformer
        // and before TypeAlternationTransformer
        return -10;
    }

    public function getRequiredPhpVersionId(): int
    {
        return 7_00_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(':');
    }

    public function process(Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$tokens[$index]->equals(':')) {
                continue;
            }

            $endIndex = $tokens->getPrevMeaningfulToken($index);

            if ($tokens[$tokens->getPrevMeaningfulToken($endIndex)]->isGivenKind(FCT::T_ENUM)) {
                $tokens[$index] = new Token([CT::T_TYPE_COLON, ':']);

                continue;
            }

            if (!$tokens[$endIndex]->equals(')')) {
                continue;
            }

            $startIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $endIndex);
            $prevIndex = $tokens->getPrevMeaningfulToken($startIndex);
            $prevToken = $tokens[$prevIndex];

            // if this could be a function name we need to take one more step
            if ($prevToken->isGivenKind(\T_STRING)) {
                $prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
                $prevToken = $tokens[$prevIndex];
            }

            if ($prevToken->isGivenKind([\T_FUNCTION, CT::T_RETURN_REF, CT::T_USE_LAMBDA, \T_FN])) {
                $tokens[$index] = new Token([CT::T_TYPE_COLON, ':']);
            }
        }
    }

    public function getCustomTokens(): array
    {
        return [CT::T_TYPE_COLON];
    }
}
