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
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Processor\ImportProcessor;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Transform T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED and T_NAME_RELATIVE into T_NAMESPACE T_NS_SEPARATOR T_STRING.
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NameQualifiedTransformer extends AbstractTransformer
{
    public function getPriority(): int
    {
        return 1; // must run before NamespaceOperatorTransformer
    }

    public function getRequiredPhpVersionId(): int
    {
        return 8_00_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([FCT::T_NAME_QUALIFIED, FCT::T_NAME_FULLY_QUALIFIED, FCT::T_NAME_RELATIVE]);
    }

    public function process(Tokens $tokens): void
    {
        $slices = [];

        foreach ($tokens as $index => $token) {
            $id = $token->getId();

            if (
                FCT::T_NAME_QUALIFIED !== $id
                && FCT::T_NAME_FULLY_QUALIFIED !== $id
                && FCT::T_NAME_RELATIVE !== $id
            ) {
                continue;
            }

            $content = $token->getContent();
            \assert('' !== $content);

            $newTokens = ImportProcessor::tokenizeName($content);

            if (FCT::T_NAME_RELATIVE === $id) {
                $newTokens[0] = new Token([\T_NAMESPACE, 'namespace']);
            }

            $slices[$index] = $newTokens;
            $tokens->clearAt($index);
        }

        $tokens->insertSlices($slices);
    }

    public function getCustomTokens(): array
    {
        return [];
    }
}
