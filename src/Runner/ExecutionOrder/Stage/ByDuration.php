<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ExecutionOrder\Stage;

use function usort;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ExecutionOrder\Context;
use PHPUnit\Runner\ExecutionOrder\Direction;
use PHPUnit\Runner\ExecutionOrder\ReorderStage;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;

/**
 * Sorts tests by the time they took during the previous test run.
 *
 * The duration of a test suite is the sum of the durations of the tests it
 * contains, so that test suites are sorted relative to each other by the time
 * they take as a whole.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ByDuration implements ReorderStage
{
    private Direction $direction;

    public function __construct(Direction $direction)
    {
        $this->direction = $direction;
    }

    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    public function apply(array $tests, Context $context): array
    {
        $testRunHistory = $context->testRunHistory();

        if ($this->direction === Direction::Ascending) {
            usort(
                $tests,
                fn (Test $left, Test $right) => $this->weight($left, $testRunHistory) <=> $this->weight($right, $testRunHistory),
            );

            return $tests;
        }

        usort(
            $tests,
            fn (Test $left, Test $right) => $this->weight($right, $testRunHistory) <=> $this->weight($left, $testRunHistory),
        );

        return $tests;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        if ($this->direction === Direction::Ascending) {
            return 'duration-ascending';
        }

        return 'duration-descending';
    }

    private function weight(Test $test, TestRunHistory $testRunHistory): float
    {
        if ($test instanceof TestSuite) {
            $sum = 0.0;

            foreach ($test->tests() as $inner) {
                $sum += $this->weight($inner, $testRunHistory);
            }

            return $sum;
        }

        if ($test instanceof Reorderable) {
            return $testRunHistory->time(TestRunHistoryId::fromReorderable($test));
        }

        return 0.0;
    }
}
