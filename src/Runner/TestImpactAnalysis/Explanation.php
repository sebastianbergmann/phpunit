<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use function array_values;
use function assert;
use function count;

/**
 * Which tests are to be run, and why each of them is.
 *
 * Which tests are run is what the selection needs; why each of them is run is
 * what a developer needs in order to trust the selection, or to see that a
 * test is run for a reason that has nothing to do with what they changed.
 * Both are worked out at once, so that what is explained is what is decided.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Explanation
{
    /**
     * @var ?array<non-empty-string, ExplainedTest>
     */
    private ?array $tests;

    /**
     * @var ?non-empty-string
     */
    private ?string $reasonEverythingIsRun;
    private int $numberOfTestsThatWereConsidered;

    /**
     * @param non-empty-string $reason
     */
    public static function everything(string $reason): self
    {
        return new self(null, $reason, 0);
    }

    /**
     * @param array<non-empty-string, ExplainedTest> $tests
     */
    public static function of(array $tests, int $numberOfTestsThatWereConsidered): self
    {
        return new self($tests, null, $numberOfTestsThatWereConsidered);
    }

    /**
     * @param ?array<non-empty-string, ExplainedTest> $tests
     * @param ?non-empty-string                       $reasonEverythingIsRun
     */
    private function __construct(?array $tests, ?string $reasonEverythingIsRun, int $numberOfTestsThatWereConsidered)
    {
        $this->tests                           = $tests;
        $this->reasonEverythingIsRun           = $reasonEverythingIsRun;
        $this->numberOfTestsThatWereConsidered = $numberOfTestsThatWereConsidered;
    }

    /**
     * @phpstan-assert-if-false !null $this->tests
     *
     * @phpstan-assert-if-true !null $this->reasonEverythingIsRun
     */
    public function isEverything(): bool
    {
        return $this->tests === null;
    }

    /**
     * Why every test is run, instead of only the tests that can be affected by
     * what changed.
     *
     * @return non-empty-string
     */
    public function reasonEverythingIsRun(): string
    {
        assert($this->reasonEverythingIsRun !== null);

        return $this->reasonEverythingIsRun;
    }

    /**
     * @return list<non-empty-string>
     */
    public function testsThatAreRun(): array
    {
        if ($this->isEverything()) {
            return [];
        }

        $tests = [];

        foreach ($this->tests as $test) {
            $tests[] = $test->test();
        }

        return $tests;
    }

    /**
     * @return list<ExplainedTest>
     */
    public function asArray(): array
    {
        if ($this->isEverything()) {
            return [];
        }

        return array_values($this->tests);
    }

    /**
     * The tests that are run for one reason, in the order they were selected.
     *
     * @return list<ExplainedTest>
     */
    public function testsRunBecause(SelectionReason $reason): array
    {
        $tests = [];

        foreach ($this->asArray() as $test) {
            if ($test->reason() !== $reason) {
                continue;
            }

            $tests[] = $test;
        }

        return $tests;
    }

    public function numberOfTestsThatAreRun(): int
    {
        if ($this->isEverything()) {
            return 0;
        }

        return count($this->tests);
    }

    public function numberOfTestsThatWereConsidered(): int
    {
        return $this->numberOfTestsThatWereConsidered;
    }
}
