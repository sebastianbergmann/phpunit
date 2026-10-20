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

use PhpCsFixer\Tokenizer\AbstractTypeTransformer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Transform `&` operator into CT::T_TYPE_INTERSECTION in `function foo(Type1 & Type2 $x) {`
 * or `} catch (ExceptionType1 & ExceptionType2 $e) {`.
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TypeIntersectionTransformer extends AbstractTypeTransformer
{
    public function getPriority(): int
    {
        // needs to run after ArrayTypehintTransformer, TypeColonTransformer and AttributeTransformer
        return -15;
    }

    public function getRequiredPhpVersionId(): int
    {
        return 8_01_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG);
    }

    public function processToken(Tokens $tokens, Token $token, int $index): void
    {
        $this->doProcess($tokens, $index, [\T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG, '&']);
    }

    public function getCustomTokens(): array
    {
        return [CT::T_TYPE_INTERSECTION];
    }

    protected function replaceToken(Tokens $tokens, int $index): void
    {
        $tokens[$index] = new Token([CT::T_TYPE_INTERSECTION, '&']);
    }
}
