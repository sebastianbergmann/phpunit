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
 * Transforms attribute related Tokens.
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class AttributeTransformer extends AbstractTransformer
{
    public function getPriority(): int
    {
        // must run before all other transformers that might touch attributes
        return 200;
    }

    public function getRequiredPhpVersionId(): int
    {
        return 8_00_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_ATTRIBUTE);
    }

    public function process(Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$tokens[$index]->isGivenKind(\T_ATTRIBUTE)) {
                continue;
            }

            do {
                ++$index;

                if ($tokens[$index]->equals('(')) {
                    $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index) + 1;
                }
            } while (!$tokens[$index]->equals(']'));

            $tokens[$index] = new Token([CT::T_ATTRIBUTE_CLOSE, ']']);
        }
    }

    public function getCustomTokens(): array
    {
        return [
            CT::T_ATTRIBUTE_CLOSE,
        ];
    }
}
