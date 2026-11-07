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
 * Transform `class` class' constant from T_CLASS into CT::T_CLASS_CONSTANT.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ClassConstantTransformer extends AbstractTransformer
{
    public function getRequiredPhpVersionId(): int
    {
        return 5_05_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_CLASS, \T_STRING]);
    }

    public function process(Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->equalsAny([
                [\T_CLASS, 'class'],
                [\T_STRING, 'class'],
            ], false)) {
                continue;
            }

            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            $prevToken = $tokens[$prevIndex];

            if ($prevToken->isGivenKind(\T_DOUBLE_COLON)) {
                $tokens[$index] = new Token([CT::T_CLASS_CONSTANT, $token->getContent()]);
            }
        }
    }

    public function getCustomTokens(): array
    {
        return [CT::T_CLASS_CONSTANT];
    }
}
