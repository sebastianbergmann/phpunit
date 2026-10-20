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

use PhpCsFixer\Fixer\AbstractIncrementOperatorFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author ntzm
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class StandardizeIncrementFixer extends AbstractIncrementOperatorFixer
{
    private const EXPRESSION_END_TOKENS = [
        ';',
        ')',
        ']',
        ',',
        ':',
        [CT::T_DYNAMIC_PROP_BRACE_CLOSE],
        [CT::T_DYNAMIC_VAR_BRACE_CLOSE],
        [\T_CLOSE_TAG],
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Increment and decrement operators should be used if possible.',
            [
                new CodeSample("<?php\n\$i += 1;\n"),
                new CodeSample("<?php\n\$i -= 1;\n"),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before IncrementStyleFixer.
     * Must run after LongToShorthandOperatorFixer.
     */
    public function getPriority(): int
    {
        return 16;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_PLUS_EQUAL, \T_MINUS_EQUAL]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            $expressionEnd = $tokens[$index];
            if (!$expressionEnd->equalsAny(self::EXPRESSION_END_TOKENS)) {
                continue;
            }

            $numberIndex = $tokens->getPrevMeaningfulToken($index);
            $number = $tokens[$numberIndex];
            if (!$number->isGivenKind(\T_LNUMBER) || '1' !== $number->getContent()) {
                continue;
            }

            $operatorIndex = $tokens->getPrevMeaningfulToken($numberIndex);
            $operator = $tokens[$operatorIndex];
            if (!$operator->isGivenKind([\T_PLUS_EQUAL, \T_MINUS_EQUAL])) {
                continue;
            }

            $startIndex = $this->findStart($tokens, $operatorIndex);

            $this->clearRangeLeaveComments(
                $tokens,
                $tokens->getPrevMeaningfulToken($operatorIndex) + 1,
                $numberIndex,
            );

            $tokens->insertAt(
                $startIndex,
                new Token($operator->isGivenKind(\T_PLUS_EQUAL) ? [\T_INC, '++'] : [\T_DEC, '--']),
            );
        }
    }

    /**
     * Clear tokens in the given range unless they are comments.
     */
    private function clearRangeLeaveComments(Tokens $tokens, int $indexStart, int $indexEnd): void
    {
        for ($i = $indexStart; $i <= $indexEnd; ++$i) {
            $token = $tokens[$i];

            if ($token->isComment()) {
                continue;
            }

            if ($token->isWhitespace("\n\r")) {
                continue;
            }

            $tokens->clearAt($i);
        }
    }
}
