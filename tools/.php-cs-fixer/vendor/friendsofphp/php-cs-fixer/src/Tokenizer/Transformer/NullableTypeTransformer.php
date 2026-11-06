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
 * Transform `?` operator into CT::T_NULLABLE_TYPE in `function foo(?Bar $b) {}`.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NullableTypeTransformer extends AbstractTransformer
{
    private const TYPES = [
        '(',
        ',',
        [CT::T_TYPE_COLON],
        [CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC],
        [CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED],
        [CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE],
        [CT::T_ATTRIBUTE_CLOSE],
        [\T_PRIVATE],
        [\T_PROTECTED],
        [\T_PUBLIC],
        [\T_VAR],
        [\T_STATIC],
        [\T_CONST],
        [\T_ABSTRACT],
        [\T_FINAL],
        [FCT::T_READONLY],
        [FCT::T_PRIVATE_SET],
        [FCT::T_PROTECTED_SET],
        [FCT::T_PUBLIC_SET],
    ];

    public function getPriority(): int
    {
        // needs to run after TypeColonTransformer
        return -20;
    }

    public function getRequiredPhpVersionId(): int
    {
        return 7_01_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('?');
    }

    public function process(Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->equals('?')) {
                continue;
            }

            $prevIndex = $tokens->getPrevMeaningfulToken($index);

            if (!$tokens[$prevIndex]->equalsAny(self::TYPES)) {
                continue;
            }

            if (
                $tokens[$prevIndex]->isGivenKind(\T_STATIC)
                && $tokens[$tokens->getPrevMeaningfulToken($prevIndex)]->isGivenKind(\T_INSTANCEOF)
            ) {
                continue;
            }

            $tokens[$index] = new Token([CT::T_NULLABLE_TYPE, '?']);
        }
    }

    public function getCustomTokens(): array
    {
        return [CT::T_NULLABLE_TYPE];
    }
}
