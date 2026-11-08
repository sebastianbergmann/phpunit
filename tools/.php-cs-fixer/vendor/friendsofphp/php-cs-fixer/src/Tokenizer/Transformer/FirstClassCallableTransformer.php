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
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FirstClassCallableTransformer extends AbstractTransformer
{
    public function getRequiredPhpVersionId(): int
    {
        return 8_01_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_ELLIPSIS);
    }

    public function process(Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (
                $token->isGivenKind(\T_ELLIPSIS)
                && $tokens[$tokens->getPrevMeaningfulToken($index)]->equals('(')
                && $tokens[$tokens->getNextMeaningfulToken($index)]->equals(')')
            ) {
                $tokens[$index] = new Token([CT::T_FIRST_CLASS_CALLABLE, '...']);
            }
        }
    }

    public function getCustomTokens(): array
    {
        return [
            CT::T_FIRST_CLASS_CALLABLE,
        ];
    }
}
