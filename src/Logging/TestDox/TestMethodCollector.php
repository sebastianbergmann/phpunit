<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function array_keys;
use function array_values;
use function assert;
use function ksort;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\TestDouble\MockObjectCreated;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestMethodCollector
{
    /**
     * @psalm-var array<class-string,array{test: TestMethod, duration: Duration, status: TestStatus}>
     */
    private array $tests        = [];
    private ?HRTime $time       = null;
    private ?TestStatus $status = null;

    /**
     * @psalm-var list<class-string>
     */
    private array $testDoubles = [];

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct()
    {
        $this->registerSubscribers();
    }

    /**
     * @psalm-return array<class-string,array{test: TestMethod, duration: Duration, status: TestStatus, testDoubles: list<class-string>}>
     */
    public function testMethodsGroupedByClassAndSortedByLine(): array
    {
        $tests = $this->tests;

        foreach (array_keys($tests) as $key) {
            ksort($tests[$key]);

            $tests[$key] = array_values($tests[$key]);
        }

        return $tests;
    }

    public function testPrepared(Prepared $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $this->time   = $event->telemetryInfo()->time();
        $this->status = TestStatus::unknown();
    }

    public function testErrored(Errored $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $this->status = TestStatus::error($event->throwable()->message());
    }

    public function testFailed(Failed $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $this->status = TestStatus::failure($event->throwable()->message());
    }

    public function testPassed(Passed $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $this->status = TestStatus::success();
    }

    public function testSkipped(Skipped $event): void
    {
        $this->status = TestStatus::skipped($event->message());
    }

    public function testMarkedIncomplete(MarkedIncomplete $event): void
    {
        $this->status = TestStatus::incomplete($event->throwable()->message());
    }

    public function testConsideredRisky(ConsideredRisky $event): void
    {
        $this->status = TestStatus::risky($event->message());
    }

    public function testCreatedTestDouble(MockObjectCreated $event): void
    {
        $this->testDoubles[] = $event->className();
    }

    public function testFinished(Finished $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $test = $event->test();

        assert($test instanceof TestMethod);

        if (!isset($this->tests[$test->className()])) {
            $this->tests[$test->className()] = [];
        }

        $this->tests[$test->className()][$test->line()] = [
            'test'        => $test,
            'duration'    => $event->telemetryInfo()->time()->duration($this->time),
            'status'      => $this->status,
            'testDoubles' => $this->testDoubles,
        ];

        $this->time        = null;
        $this->status      = null;
        $this->testDoubles = [];
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(): void
    {
        Facade::registerSubscriber(new TestCreatedMockObjectSubscriber($this));
        Facade::registerSubscriber(new TestConsideredRiskySubscriber($this));
        Facade::registerSubscriber(new TestErroredSubscriber($this));
        Facade::registerSubscriber(new TestFailedSubscriber($this));
        Facade::registerSubscriber(new TestFinishedSubscriber($this));
        Facade::registerSubscriber(new TestMarkedIncompleteSubscriber($this));
        Facade::registerSubscriber(new TestPassedSubscriber($this));
        Facade::registerSubscriber(new TestPreparedSubscriber($this));
        Facade::registerSubscriber(new TestSkippedSubscriber($this));
    }
}
