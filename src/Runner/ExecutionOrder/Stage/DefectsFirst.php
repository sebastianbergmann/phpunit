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

use function assert;
use function max;
use function usort;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\Test;
use PHPUnit\Runner\ExecutionOrder\Context;
use PHPUnit\Runner\ExecutionOrder\DefectWeightPolicy;
use PHPUnit\Runner\ExecutionOrder\ReorderStage;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;

/**
 * Hoists the tests that were defective during the previous test run towards
 * the front, so that a defect is reached as fast as possible.
 *
 * Tests of equal weight, including equally weighted defective tests, keep
 * their relative order, so that the order established by a preceding stage is
 * preserved by PHP's stable usort() function.
 *
 * The weight of a test suite is the largest weight among the tests it
 * contains. Because the tests of a test suite are reordered before the test
 * suite itself is reordered within its parent, weights propagate upwards
 * through arbitrarily deeply nested test suites.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DefectsFirst implements ReorderStage
{
    private readonly DefectWeightPolicy $weightPolicy;

    /**
     * @var array<string, non-negative-int>
     */
    private array $weights = [];

    public function __construct(DefectWeightPolicy $weightPolicy)
    {
        $this->weightPolicy = $weightPolicy;
    }

    /**
     * @param list<Test> $tests
     *
     * @return list<Test>
     */
    public function apply(array $tests, Context $context): array
    {
        $this->recordWeights($tests, $context);

        usort(
            $tests,
            fn (Test $left, Test $right) => $this->weightOf($right) <=> $this->weightOf($left),
        );

        return $tests;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return 'defects';
    }

    /**
     * @param list<Test> $tests
     */
    private function recordWeights(array $tests, Context $context): void
    {
        $max = 0;

        foreach ($tests as $test) {
            assert($test instanceof Reorderable);

            $sortId = $test->sortId();

            if (!isset($this->weights[$sortId])) {
                $this->weights[$sortId] = $this->weightPolicy->weight(
                    $context->testRunHistory()->status(
                        TestRunHistoryId::fromReorderable($test),
                    ),
                );
            }

            $max = max($max, $this->weights[$sortId]);
        }

        $this->weights[$context->testSuite()->sortId()] = $max;
    }

    /**
     * recordWeights() has recorded a weight for every test that is compared
     * here, so the lookup cannot fail.
     *
     * @return non-negative-int
     */
    private function weightOf(Test $test): int
    {
        assert($test instanceof Reorderable);

        $sortId = $test->sortId();

        assert(isset($this->weights[$sortId]));

        return $this->weights[$sortId];
    }
}
