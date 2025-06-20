<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Indicator\PhpUnitTestCaseIndicator;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Utils;
use PhpCsFixerCustomFixers\TokenRemover;

/**
 * @no-named-arguments
 */
final class PhpUnitNoUselessReturnFixer extends AbstractFixer
{
    private const FUNCTION_TOKENS = [[\T_STRING, 'fail'], [\T_STRING, 'markTestIncomplete'], [\T_STRING, 'markTestSkipped']];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            \sprintf(
                'PHPUnit %s functions must not be directly followed by `return`.',
                Utils::naturalLanguageJoinWithBackticks(\array_map(
                    static fn (array $token): string => $token[1],
                    self::FUNCTION_TOKENS,
                )),
            ),
            [new CodeSample('<?php
class FooTest extends TestCase {
    public function testFoo() {
        $this->markTestSkipped();
        return;
    }
}
')],
            'They will throw an exception anyway.',
            'when original PHPUnit methods are overwritten',
        );
    }

    /**
     * Must run before NoExtraBlankLinesFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_EXTENDS, \T_FUNCTION, \T_STRING, \T_RETURN]);
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $phpUnitTestCaseIndicator = new PhpUnitTestCaseIndicator();

        /** @var list<int> $indices */
        foreach ($phpUnitTestCaseIndicator->findPhpUnitClasses($tokens) as $indices) {
            $this->removeUselessReturns($tokens, $indices[0], $indices[1]);
        }
    }

    private function removeUselessReturns(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();

        for ($index = $startIndex; $index < $endIndex; $index++) {
            if (!$tokens[$index]->equalsAny(self::FUNCTION_TOKENS, false)) {
                continue;
            }

            $openingBraceIndex = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($openingBraceIndex));

            if (!$tokens[$openingBraceIndex]->equals('(')) {
                continue;
            }

            if (!$functionsAnalyzer->isTheSameClassCall($tokens, $index)) {
                continue;
            }

            $closingBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openingBraceIndex);

            $semicolonIndex = $tokens->getNextMeaningfulToken($closingBraceIndex);
            \assert(\is_int($semicolonIndex));

            $returnIndex = $tokens->getNextMeaningfulToken($semicolonIndex);
            \assert(\is_int($returnIndex));

            if (!$tokens[$returnIndex]->isGivenKind(\T_RETURN)) {
                continue;
            }

            $semicolonAfterReturnIndex = $tokens->getNextTokenOfKind($returnIndex, [';', '(']);
            \assert(\is_int($semicolonAfterReturnIndex));

            while ($tokens[$semicolonAfterReturnIndex]->equals('(')) {
                $closingBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $semicolonAfterReturnIndex);

                $semicolonAfterReturnIndex = $tokens->getNextTokenOfKind($closingBraceIndex, [';', '(']);
                \assert(\is_int($semicolonAfterReturnIndex));
            }

            $tokens->clearRange($returnIndex, $semicolonAfterReturnIndex - 1);
            TokenRemover::removeWithLinesIfPossible($tokens, $semicolonAfterReturnIndex);
        }
    }
}
