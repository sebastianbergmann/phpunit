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

namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ReturnToYieldFromFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'If the function explicitly returns an array, and has the return type `iterable`, then `yield from` must be used instead of `return`.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php function giveMeData(): iterable {
                            return [1, 2, 3];
                        }

                        PHP,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_FUNCTION, \T_RETURN]) && $tokens->isAnyTokenKindsFound([\T_ARRAY, CT::T_ARRAY_BRACKET_OPEN]);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before YieldFromArrayToYieldsFixer.
     * Must run after PhpUnitDataProviderReturnTypeFixer, PhpdocToReturnTypeFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens->findGivenKind(\T_RETURN) as $index => $token) {
            if (!$this->shouldBeFixed($tokens, $index)) {
                continue;
            }

            $tokens[$index] = new Token([\T_YIELD_FROM, 'yield from']);
        }
    }

    private function shouldBeFixed(Tokens $tokens, int $returnIndex): bool
    {
        $arrayStartIndex = $tokens->getNextMeaningfulToken($returnIndex);
        if (!$tokens[$arrayStartIndex]->isGivenKind([\T_ARRAY, CT::T_ARRAY_BRACKET_OPEN])) {
            return false;
        }

        if ($tokens[$arrayStartIndex]->isGivenKind(CT::T_ARRAY_BRACKET_OPEN)) {
            $arrayEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_BRACKET, $arrayStartIndex);
        } else {
            $arrayOpenParenthesisIndex = $tokens->getNextTokenOfKind($arrayStartIndex, ['(']);
            $arrayEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $arrayOpenParenthesisIndex);
        }

        $functionEndIndex = $arrayEndIndex;
        do {
            $functionEndIndex = $tokens->getNextMeaningfulToken($functionEndIndex);
        } while (null !== $functionEndIndex && $tokens[$functionEndIndex]->equals(';'));
        if (null === $functionEndIndex || !$tokens[$functionEndIndex]->equals('}')) {
            return false;
        }

        $functionStartIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_BRACE, $functionEndIndex);

        $returnTypeIndex = $tokens->getPrevMeaningfulToken($functionStartIndex);
        if (!$tokens[$returnTypeIndex]->isGivenKind(\T_STRING)) {
            return false;
        }

        if ('iterable' !== strtolower($tokens[$returnTypeIndex]->getContent())) {
            return false;
        }

        $beforeReturnTypeIndex = $tokens->getPrevMeaningfulToken($returnTypeIndex);

        return $tokens[$beforeReturnTypeIndex]->isGivenKind(CT::T_TYPE_COLON);
    }
}
