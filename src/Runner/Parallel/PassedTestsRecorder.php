<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function assert;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\CollectingDispatcher;
use PHPUnit\Event\TestSuite\TestSuite;
use PHPUnit\Event\TestSuite\TestSuiteForRepeatedTestMethod;
use PHPUnit\Event\TestSuite\TestSuiteForTestMethodWithDataProvider;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\TestRunner\TestResult\PassedTests;

/**
 * Records, inside a worker process, that a test method whose test is run more
 * than once passed, so that the tests of the same unit that depend on it can
 * run.
 *
 * A test method whose test is run once records itself as passed (see
 * TestCase::registerAsPassed()). A test method whose test is run with the
 * data sets of a data provider, or repeated, is recorded as passed once all
 * of its runs have finished and none of them failed or errored. In the main
 * process, the test result collector records this (see
 * Collector::testSuiteFinished()). The collector of a worker process does not
 * receive the events of the units it runs, as the main process owns the test
 * result, so the worker records these passes from the events of each unit it
 * runs with a recorder of its own.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PassedTestsRecorder
{
    /**
     * @var array<non-empty-string, true>
     */
    private array $testMethodsThatFailedOrErrored = [];

    /**
     * @throws UnknownSubscriberTypeException
     */
    public function registerWith(CollectingDispatcher $dispatcher): void
    {
        $dispatcher->registerSubscriber(new TestFailedSubscriber($this));
        $dispatcher->registerSubscriber(new TestErroredSubscriber($this));
        $dispatcher->registerSubscriber(new TestSuiteFinishedSubscriber($this));
    }

    public function testFailedOrErrored(Test $test): void
    {
        if (!$test->isTestMethod()) {
            return;
        }

        $this->testMethodsThatFailedOrErrored[$test->className() . '::' . $test->methodName()] = true;
    }

    public function testSuiteFinished(TestSuite $testSuite): void
    {
        if ($testSuite->isForTestMethodWithDataProvider()) {
            assert($testSuite instanceof TestSuiteForTestMethodWithDataProvider);

            $this->registerTestMethodAsPassedIfNoRunFailedOrErrored($testSuite);

            return;
        }

        if ($testSuite->isForRepeatedTestMethod()) {
            assert($testSuite instanceof TestSuiteForRepeatedTestMethod);

            // for a repeated data set, the enclosing data provider test suite decides
            // whether the test method passed once all of its data sets have finished
            if (!$testSuite->isForDataSet()) {
                $this->registerTestMethodAsPassedIfNoRunFailedOrErrored($testSuite);
            }
        }
    }

    private function registerTestMethodAsPassedIfNoRunFailedOrErrored(TestSuiteForRepeatedTestMethod|TestSuiteForTestMethodWithDataProvider $testSuite): void
    {
        $tests = $testSuite->tests()->asArray();

        assert($tests !== []);

        $test = $tests[0];

        assert($test instanceof TestMethod);

        if (isset($this->testMethodsThatFailedOrErrored[$test->className() . '::' . $test->methodName()])) {
            return;
        }

        PassedTests::instance()->testMethodPassed($test, null);
    }
}
