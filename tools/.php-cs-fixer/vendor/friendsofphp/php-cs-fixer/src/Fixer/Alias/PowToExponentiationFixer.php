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

namespace PhpCsFixer\Fixer\Alias;

use PhpCsFixer\AbstractFunctionReferenceFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PowToExponentiationFixer extends AbstractFunctionReferenceFixer
{
    public function isCandidate(Tokens $tokens): bool
    {
        // minimal candidate to fix is seven tokens: pow(x,y);
        return $tokens->count() > 7 && $tokens->isTokenKindFound(\T_STRING);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Converts `pow` to the `**` operator.',
            [
                new CodeSample(
                    "<?php\n pow(\$a, 1);\n",
                ),
            ],
            null,
            'Risky when the function `pow` is overridden.',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before BinaryOperatorSpacesFixer, MethodArgumentSpaceFixer, NativeFunctionCasingFixer, NoSpacesAfterFunctionNameFixer, NoSpacesInsideParenthesisFixer, SpacesInsideParenthesesFixer.
     */
    public function getPriority(): int
    {
        return 32;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $candidates = $this->findPowCalls($tokens);
        $argumentsAnalyzer = new ArgumentsAnalyzer();
        $numberOfTokensAdded = 0;
        $previousCloseParenthesisIndex = \count($tokens);

        foreach (array_reverse($candidates) as $candidate) {
            // if in the previous iteration(s) tokens were added to the collection and this is done within the tokens
            // indices of the current candidate than the index of the close ')' of the candidate has moved and so
            // the index needs to be updated
            if ($previousCloseParenthesisIndex < $candidate[2]) {
                $previousCloseParenthesisIndex = $candidate[2];
                $candidate[2] += $numberOfTokensAdded;
            } else {
                $previousCloseParenthesisIndex = $candidate[2];
                $numberOfTokensAdded = 0;
            }

            $arguments = $argumentsAnalyzer->getArguments($tokens, $candidate[1], $candidate[2]);

            if (2 !== \count($arguments)) {
                continue;
            }

            for ($i = $candidate[1]; $i < $candidate[2]; ++$i) {
                if ($tokens[$i]->isGivenKind(\T_ELLIPSIS)) {
                    continue 2;
                }
            }

            $numberOfTokensAdded += $this->fixPowToExponentiation(
                $tokens,
                $candidate[0], // functionNameIndex,
                $candidate[1], // openParenthesisIndex,
                $candidate[2], // closeParenthesisIndex,
                $arguments,
            );
        }
    }

    /**
     * @return list<array{int, int, int}>
     */
    private function findPowCalls(Tokens $tokens): array
    {
        $candidates = [];

        // Minimal candidate to fix is seven tokens: pow(x,y);
        $end = \count($tokens) - 6;

        // First possible location is after the open token: 1
        for ($i = 1; $i < $end; ++$i) {
            $candidate = $this->find('pow', $tokens, $i, $end);

            if (null === $candidate) {
                break;
            }

            $i = $candidate[1]; // proceed to openParenthesisIndex
            $candidates[] = $candidate;
        }

        return $candidates;
    }

    /**
     * @param array<int, int> $arguments
     *
     * @return int number of tokens added to the collection
     */
    private function fixPowToExponentiation(Tokens $tokens, int $functionNameIndex, int $openParenthesisIndex, int $closeParenthesisIndex, array $arguments): int
    {
        // find the argument separator ',' directly after the last token of the first argument;
        // replace it with T_POW '**'
        \assert(false !== reset($arguments));
        $tokens[$tokens->getNextTokenOfKind(reset($arguments), [','])] = new Token([\T_POW, '**']);

        // clean up the function call tokens prt. I
        $tokens->clearAt($closeParenthesisIndex);
        $previousIndex = $tokens->getPrevMeaningfulToken($closeParenthesisIndex);

        if ($tokens[$previousIndex]->equals(',')) {
            $tokens->clearAt($previousIndex); // trailing ',' in function call (PHP 7.3)
        }

        $added = 0;

        // check if the arguments need to be wrapped in parentheses
        foreach (array_reverse($arguments, true) as $argumentStartIndex => $argumentEndIndex) {
            if ($this->isParenthesisNeeded($tokens, $argumentStartIndex, $argumentEndIndex)) {
                $tokens->insertAt($argumentEndIndex + 1, new Token(')'));
                $tokens->insertAt($argumentStartIndex, new Token('('));
                $added += 2;
            }
        }

        // clean up the function call tokens prt. II
        $tokens->clearAt($openParenthesisIndex);
        $tokens->clearTokenAndMergeSurroundingWhitespace($functionNameIndex);

        $prevMeaningfulTokenIndex = $tokens->getPrevMeaningfulToken($functionNameIndex);

        if ($tokens[$prevMeaningfulTokenIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $tokens->clearAt($prevMeaningfulTokenIndex);
        }

        return $added;
    }

    private function isParenthesisNeeded(Tokens $tokens, int $argumentStartIndex, int $argumentEndIndex): bool
    {
        static $allowedKinds = null;

        if (null === $allowedKinds) {
            $allowedKinds = $this->getAllowedKinds();
        }

        for ($i = $argumentStartIndex; $i <= $argumentEndIndex; ++$i) {
            if ($tokens[$i]->isGivenKind($allowedKinds) || $tokens->isEmptyAt($i)) {
                continue;
            }

            $blockType = Tokens::detectBlockType($tokens[$i]);

            if (null !== $blockType) {
                $i = $tokens->findBlockEnd($blockType['type'], $i);

                continue;
            }

            if ($tokens[$i]->equals('$')) {
                $i = $tokens->getNextMeaningfulToken($i);
                if ($tokens[$i]->isGivenKind(CT::T_DYNAMIC_VAR_BRACE_OPEN)) {
                    $i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_DYNAMIC_VAR_BRACE, $i);

                    continue;
                }
            }

            if ($tokens[$i]->equals('+') && $tokens->getPrevMeaningfulToken($i) < $argumentStartIndex) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return non-empty-list<int>
     */
    private function getAllowedKinds(): array
    {
        return [
            \T_DNUMBER, \T_LNUMBER, \T_VARIABLE, \T_STRING, \T_CONSTANT_ENCAPSED_STRING, \T_DOUBLE_CAST,
            \T_INT_CAST, \T_INC, \T_DEC, \T_NS_SEPARATOR, \T_WHITESPACE, \T_DOUBLE_COLON, \T_LINE, \T_COMMENT, \T_DOC_COMMENT,
            CT::T_NAMESPACE_OPERATOR,
            ...Token::getObjectOperatorKinds(),
        ];
    }
}
