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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Move trailing whitespaces from comments and docs into following T_WHITESPACE token.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class WhitespacyCommentTransformer extends AbstractTransformer
{
    public function getRequiredPhpVersionId(): int
    {
        return 5_00_00;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_COMMENT, \T_DOC_COMMENT]);
    }

    public function process(Tokens $tokens): void
    {
        $slices = [];

        foreach ($tokens as $index => $token) {
            if (!$token->isComment()) {
                continue;
            }

            $content = $token->getContent();
            $trimmedContent = rtrim($content);

            // nothing trimmed, nothing to do
            if ($content === $trimmedContent) {
                continue;
            }

            $whitespaces = substr($content, \strlen($trimmedContent));

            $tokens[$index] = new Token([$token->getId(), $trimmedContent]);

            if (isset($tokens[$index + 1]) && $tokens[$index + 1]->isWhitespace()) {
                $tokens[$index + 1] = new Token([\T_WHITESPACE, $whitespaces.$tokens[$index + 1]->getContent()]);
            } else {
                $slices[$index + 1] = new Token([\T_WHITESPACE, $whitespaces]);
            }
        }

        if ([] !== $slices) {
            $tokens->insertSlices($slices);
        }
    }

    public function getCustomTokens(): array
    {
        return [];
    }
}
