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

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\RangeAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @phpstan-import-type _PhpTokenPrototypePartial from Token
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TernaryToElvisOperatorFixer extends AbstractFixer
{
    /**
     * Lower precedence and other valid preceding tokens.
     *
     * Ordered by most common types first.
     *
     * @var non-empty-list<_PhpTokenPrototypePartial>
     */
    private const VALID_BEFORE_ENDTYPES = [
        '=',
        [\T_OPEN_TAG],
        [\T_OPEN_TAG_WITH_ECHO],
        '(',
        ',',
        ';',
        '[',
        '{',
        '}',
        [CT::T_ARRAY_INDEX_BRACE_OPEN],
        [\T_AND_EQUAL],    // &=
        [\T_CONCAT_EQUAL], // .=
        [\T_DIV_EQUAL],    // /=
        [\T_MINUS_EQUAL],  // -=
        [\T_MOD_EQUAL],    // %=
        [\T_MUL_EQUAL],    // *=
        [\T_OR_EQUAL],     // |=
        [\T_PLUS_EQUAL],   // +=
        [\T_POW_EQUAL],    // **=
        [\T_SL_EQUAL],     // <<=
        [\T_SR_EQUAL],     // >>=
        [\T_XOR_EQUAL],    // ^=
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Use the Elvis operator `?:` where possible.',
            [
                new CodeSample(
                    "<?php\n\$foo = \$foo ? \$foo : 1;\n",
                ),
                new CodeSample(
                    "<?php \$foo = \$bar[a()] ? \$bar[a()] : 1; # \"risky\" sample, \"a()\" only gets called once after fixing\n",
                ),
            ],
            null,
            'Risky when relying on functions called on both sides of the `?` operator.',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before NoTrailingWhitespaceFixer, TernaryOperatorSpacesFixer.
     */
    public function getPriority(): int
    {
        return 2;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('?');
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = \count($tokens) - 5; $index > 1; --$index) {
            if (!$tokens[$index]->equals('?')) {
                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($index);

            if ($tokens[$nextIndex]->equals(':')) {
                continue; // Elvis is alive!
            }

            // get and check what is before the `?` operator

            $beforeOperator = $this->getBeforeOperator($tokens, $index);

            if (null === $beforeOperator) {
                continue; // contains something we cannot fix because of priorities
            }

            // get what is after the `?` token

            $afterOperator = $this->getAfterOperator($tokens, $index);

            // if before and after the `?` operator are the same (in meaningful matter), clear after

            if (RangeAnalyzer::rangeEqualsRange($tokens, $beforeOperator, $afterOperator)) {
                $this->clearMeaningfulFromRange($tokens, $afterOperator);
            }
        }
    }

    /**
     * @return ?array{start: int, end: int} null if contains ++/-- operator
     */
    private function getBeforeOperator(Tokens $tokens, int $index): ?array
    {
        $blockEdgeDefinitions = Tokens::getBlockEdgeDefinitions();
        $index = $tokens->getPrevMeaningfulToken($index);
        $before = ['end' => $index];

        while (!$tokens[$index]->equalsAny(self::VALID_BEFORE_ENDTYPES)) {
            if ($tokens[$index]->isGivenKind([\T_INC, \T_DEC])) {
                return null;
            }

            $detectedBlockType = Tokens::detectBlockType($tokens[$index]);

            if (null === $detectedBlockType || $detectedBlockType['isStart']) {
                $before['start'] = $index;
                $index = $tokens->getPrevMeaningfulToken($index);

                continue;
            }

            /** @phpstan-ignore-next-line offsetAccess.notFound (we just detected block type, we know it's definition exists under given PHP runtime) */
            $blockType = $blockEdgeDefinitions[$detectedBlockType['type']];
            $openCount = 1;

            do {
                $index = $tokens->getPrevMeaningfulToken($index);

                if ($tokens[$index]->isGivenKind([\T_INC, \T_DEC])) {
                    return null;
                }

                if ($tokens[$index]->equals($blockType['start'])) {
                    ++$openCount;

                    continue;
                }

                if ($tokens[$index]->equals($blockType['end'])) {
                    --$openCount;
                }
            } while (1 >= $openCount);

            $before['start'] = $index;
            $index = $tokens->getPrevMeaningfulToken($index);
        }

        if (!isset($before['start'])) {
            return null;
        }

        return $before;
    }

    /**
     * @return array{start: int, end: int}
     */
    private function getAfterOperator(Tokens $tokens, int $index): array
    {
        $index = $tokens->getNextMeaningfulToken($index);
        $after = ['start' => $index];

        do {
            $blockType = Tokens::detectBlockType($tokens[$index]);

            if (null !== $blockType) {
                $index = $tokens->findBlockEnd($blockType['type'], $index);
            }

            $after['end'] = $index;
            $index = $tokens->getNextMeaningfulToken($index);
        } while (!$tokens[$index]->equals(':'));

        return $after;
    }

    /**
     * @param array{start: int, end: int} $range
     */
    private function clearMeaningfulFromRange(Tokens $tokens, array $range): void
    {
        // $range['end'] must be meaningful!
        for ($i = $range['end']; $i >= $range['start']; $i = $tokens->getPrevMeaningfulToken($i)) {
            $tokens->clearTokenAndMergeSurroundingWhitespace($i);
        }
    }
}
