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

use function array_column;
use function array_key_exists;
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
final class ByDuration implements ReorderStage
{
    private readonly Direction $direction;

    /**
     * @var array<string, float>
     */
    private array $weights = [];

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
        $weighted       = [];

        foreach ($tests as $test) {
            $weighted[] = [$this->weight($test, $testRunHistory), $test];
        }

        if ($this->direction === Direction::Ascending) {
            usort($weighted, static fn (array $left, array $right) => $left[0] <=> $right[0]);
        } else {
            usort($weighted, static fn (array $left, array $right) => $right[0] <=> $left[0]);
        }

        return array_column($weighted, 1);
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
            return $this->weightOfTestSuite($test, $testRunHistory);
        }

        if ($test instanceof Reorderable) {
            return $testRunHistory->time(TestRunHistoryId::fromReorderable($test));
        }

        return 0.0;
    }

    /**
     * The weight of a test suite is needed when its parent test suite is
     * reordered and again when the weight of that parent test suite is needed
     * one level further up. Remembering it keeps the tree from being walked
     * once per level.
     */
    private function weightOfTestSuite(TestSuite $testSuite, TestRunHistory $testRunHistory): float
    {
        $sortId = $testSuite->sortId();

        if (array_key_exists($sortId, $this->weights)) {
            return $this->weights[$sortId];
        }

        $weight = 0.0;

        foreach ($testSuite->tests() as $test) {
            $weight += $this->weight($test, $testRunHistory);
        }

        $this->weights[$sortId] = $weight;

        return $weight;
    }
}
