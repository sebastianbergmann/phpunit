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

namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\Fixer\AbstractShortOperatorFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class AssignNullCoalescingToCoalesceEqualFixer extends AbstractShortOperatorFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Use the null coalescing assignment operator `??=` where possible.',
            [
                new CodeSample(
                    "<?php\n\$foo = \$foo ?? 1;\n",
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before BinaryOperatorSpacesFixer, NoWhitespaceInBlankLineFixer.
     * Must run after TernaryToNullCoalescingFixer.
     */
    public function getPriority(): int
    {
        return -1;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_COALESCE);
    }

    protected function isOperatorTokenCandidate(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->isGivenKind(\T_COALESCE)) {
            return false;
        }

        // make sure after '??' does not contain '? :'

        $nextIndex = $tokens->getNextTokenOfKind($index, ['?', ';', [\T_CLOSE_TAG]]);

        return null === $nextIndex || !$tokens[$nextIndex]->equals('?');
    }

    protected function getReplacementToken(Token $token): Token
    {
        return new Token([\T_COALESCE_EQUAL, '??=']);
    }
}
