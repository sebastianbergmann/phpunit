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
 * Transform braced class instantiation braces in `(new Foo())` into CT::T_CLASS_INSTANTIATION_PARENTHESIS_OPEN
 * and CT::T_CLASS_INSTANTIATION_PARENTHESIS_CLOSE.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class BraceClassInstantiationTransformer extends AbstractTransformer
{
    public function getPriority(): int
    {
        // must run after CurlyBraceTransformer and SquareBraceTransformer
        return -2;
    }

    public function getRequiredPhpVersionId(): int
    {
        return 5_00_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_NEW);
    }

    public function process(Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$tokens[$index]->equals('(') || !$tokens[$tokens->getNextMeaningfulToken($index)]->isGivenKind(\T_NEW)) {
                continue;
            }

            if ($tokens[$tokens->getPrevMeaningfulToken($index)]->equalsAny([
                ')',
                ']',
                [CT::T_ARRAY_INDEX_BRACE_CLOSE],
                [CT::T_ARRAY_BRACKET_CLOSE],
                [CT::T_CLASS_INSTANTIATION_PARENTHESIS_CLOSE],
                [\T_ARRAY],
                [\T_CLASS],
                [\T_ELSEIF],
                [\T_FOR],
                [\T_FOREACH],
                [\T_IF],
                [\T_STATIC],
                [\T_STRING],
                [\T_SWITCH],
                [\T_VARIABLE],
                [\T_WHILE],
            ])) {
                continue;
            }

            $closeIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index);

            $tokens[$index] = new Token([CT::T_CLASS_INSTANTIATION_PARENTHESIS_OPEN, '(']);
            $tokens[$closeIndex] = new Token([CT::T_CLASS_INSTANTIATION_PARENTHESIS_CLOSE, ')']);
        }
    }

    public function getCustomTokens(): array
    {
        return [CT::T_CLASS_INSTANTIATION_PARENTHESIS_OPEN, CT::T_CLASS_INSTANTIATION_PARENTHESIS_CLOSE];
    }
}
