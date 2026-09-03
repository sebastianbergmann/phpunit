<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ExecutionOrder;

use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;

/**
 * The state a reordering stage needs in order to transform the tests of a
 * single test suite: the test suite itself and the history of previous test
 * runs.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Context
{
    private TestSuite $testSuite;
    private TestRunHistory $testRunHistory;

    public function __construct(TestSuite $testSuite, TestRunHistory $testRunHistory)
    {
        $this->testSuite      = $testSuite;
        $this->testRunHistory = $testRunHistory;
    }

    public function testSuite(): TestSuite
    {
        return $this->testSuite;
    }

    public function testRunHistory(): TestRunHistory
    {
        return $this->testRunHistory;
    }
}
