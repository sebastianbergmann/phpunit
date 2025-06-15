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
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\Analyzer\Analysis\ArgumentAnalysis;
use PhpCsFixerCustomFixers\Analyzer\FunctionAnalyzer;

/**
 * @no-named-arguments
 */
final class PhpUnitAssertArgumentsOrderFixer extends AbstractFixer
{
    private const ASSERTIONS = [
        'assertequals' => true,
        'assertnotequals' => true,
        'assertequalscanonicalizing' => true,
        'assertnotequalscanonicalizing' => true,
        'assertequalsignoringcase' => true,
        'assertnotequalsignoringcase' => true,
        'assertequalswithdelta' => true,
        'assertnotequalswithdelta' => true,
        'assertsame' => true,
        'assertnotsame' => true,
        'assertgreaterthan' => 'assertLessThan',
        'assertgreaterthanorequal' => 'assertLessThanOrEqual',
        'assertlessthan' => 'assertGreaterThan',
        'assertlessthanorequal' => 'assertGreaterThanOrEqual',
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHPUnit assertions must have expected argument before actual one.',
            [new CodeSample('<?php
class FooTest extends TestCase {
    public function testFoo() {
        self::assertSame($value, 10);
    }
}
')],
            '',
            'when original PHPUnit methods are overwritten',
        );
    }

    /**
     * Must run before PhpUnitConstructFixer, PhpUnitDedicatedAssertFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_EXTENDS, \T_FUNCTION]);
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
            $this->fixArgumentsOrder($tokens, $indices[0], $indices[1]);
        }
    }

    private function fixArgumentsOrder(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        for ($index = $startIndex; $index < $endIndex; $index++) {
            $newAssertion = self::getNewAssertion($tokens, $index);
            if ($newAssertion === null) {
                continue;
            }

            $arguments = FunctionAnalyzer::getFunctionArguments($tokens, $index);

            if (!self::shouldArgumentsBeSwapped($arguments)) {
                continue;
            }

            if ($newAssertion !== $tokens[$index]->getContent()) {
                $tokens[$index] = new Token([\T_STRING, $newAssertion]);
            }

            self::swapArguments($tokens, $arguments);
        }
    }

    private static function getNewAssertion(Tokens $tokens, int $index): ?string
    {
        $oldAssertion = $tokens[$index]->getContent();

        if (!\array_key_exists(\strtolower($oldAssertion), self::ASSERTIONS)) {
            return null;
        }

        $newAssertion = self::ASSERTIONS[\strtolower($oldAssertion)];

        $openingBraceIndex = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($openingBraceIndex));

        if (!$tokens[$openingBraceIndex]->equals('(')) {
            return null;
        }

        if (!(new FunctionsAnalyzer())->isTheSameClassCall($tokens, $index)) {
            return null;
        }

        if (!\is_string($newAssertion)) {
            return $oldAssertion;
        }

        return $newAssertion;
    }

    /**
     * @param list<ArgumentAnalysis> $arguments
     */
    private static function shouldArgumentsBeSwapped(array $arguments): bool
    {
        if (\count($arguments) < 2) {
            return false;
        }

        if ($arguments[0]->isConstant()) {
            return false;
        }

        return $arguments[1]->isConstant();
    }

    /**
     * @param list<ArgumentAnalysis> $arguments
     */
    private static function swapArguments(Tokens $tokens, array $arguments): void
    {
        $expectedArgumentTokens = []; // these will be 1st argument
        for ($index = $arguments[1]->getStartIndex(); $index <= $arguments[1]->getEndIndex(); $index++) {
            $expectedArgumentTokens[] = $tokens[$index];
        }

        $actualArgumentTokens = []; // these will be 2nd argument
        for ($index = $arguments[0]->getStartIndex(); $index <= $arguments[0]->getEndIndex(); $index++) {
            $actualArgumentTokens[] = $tokens[$index];
        }

        $tokens->overrideRange($arguments[1]->getStartIndex(), $arguments[1]->getEndIndex(), $actualArgumentTokens);
        $tokens->overrideRange($arguments[0]->getStartIndex(), $arguments[0]->getEndIndex(), $expectedArgumentTokens);
    }
}
