<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\IterativeTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ExecutionOrder\Context;
use PHPUnit\Runner\ExecutionOrder\ReorderPipeline;
use PHPUnit\Runner\TestRunHistory\NullTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;

/**
 * Applies a ReorderPipeline to every test suite of a tree of test suites,
 * depth first, so that the tests of a test suite are reordered before the test
 * suite itself is reordered within its parent.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteSorter
{
    public const int ORDER_DEFAULT             = 0;
    public const int ORDER_RANDOMIZED          = 1;
    public const int ORDER_REVERSED            = 2;
    public const int ORDER_DEFECTS_FIRST       = 3;
    public const int ORDER_DURATION_ASCENDING  = 4;
    public const int ORDER_SIZE_ASCENDING      = 5;
    public const int ORDER_DURATION_DESCENDING = 6;
    public const int ORDER_SIZE_DESCENDING     = 7;
    private readonly TestRunHistory $testRunHistory;

    public function __construct(TestRunHistory $testRunHistory = new NullTestRunHistory)
    {
        $this->testRunHistory = $testRunHistory;
    }

    public function apply(Test $suite, ReorderPipeline $pipeline): void
    {
        // the repetitions of a repeated test and the attempts of a retried test
        // always run in their original order
        if ($suite instanceof IterativeTestSuite) {
            return;
        }

        if (!$suite instanceof TestSuite) {
            return;
        }

        foreach ($suite as $child) {
            $this->apply($child, $pipeline);
        }

        $tests = $suite->tests();

        if ($tests === []) {
            return;
        }

        $suite->setTests(
            $pipeline->apply(
                $tests,
                new Context($suite, $this->testRunHistory),
            ),
        );
    }
}
