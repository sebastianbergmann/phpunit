<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TeamCity;

use function assert;
use function getmypid;
use function ini_get;
use function sprintf;
use function stripos;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\InvalidArgumentException;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\Aborted;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\TestSuiteForTestClass;
use PHPUnit\Event\TestSuite\TestSuiteForTestMethodWithDataProvider;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Util\Exception;
use PHPUnit\Util\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TeamCityLogger extends Printer
{
    private bool $isSummaryTestCountPrinted = false;

    private ?Test $test = null;

    private ?HRTime $time = null;

    private ?int $flowId;

    /**
     * @throws EventFacadeIsSealedException
     * @throws Exception
     * @throws UnknownSubscriberTypeException
     */
    public function __construct(string $out)
    {
        parent::__construct($out);

        $this->registerSubscribers();
        $this->setFlowId();
    }

    public function testSuiteStarted(TestSuiteStarted $event): void
    {
        $testSuite = $event->testSuite();

        if (!$this->isSummaryTestCountPrinted) {
            $this->isSummaryTestCountPrinted = true;

            $this->writeMessage(
                'testCount',
                ['count' => $testSuite->count()]
            );
        }

        if ($testSuite->isWithName() && $testSuite->name() === '') {
            return;
        }

        $parameters = ['name' => $testSuite->name()];

        if ($testSuite->isForTestClass()) {
            assert($testSuite instanceof TestSuiteForTestClass);

            $parameters['locationHint'] = sprintf(
                'php_qn://%s::\\%s',
                $testSuite->file(),
                $testSuite->name()
            );
        } else {
            assert($testSuite instanceof TestSuiteForTestMethodWithDataProvider);

            $parameters['locationHint'] = sprintf(
                'php_qn://%s::\\%s',
                $testSuite->file(),
                $testSuite->name()
            );

            $parameters['name'] = $testSuite->methodName();
        }

        $this->writeMessage('testSuiteStarted', $parameters);
    }

    public function testSuiteFinished(TestSuiteFinished $event): void
    {
        $testSuite = $event->testSuite();

        if ($testSuite->isWithName() && $testSuite->name() === '') {
            return;
        }

        $parameters = ['name' => $testSuite->name()];

        if ($testSuite->isForTestMethodWithDataProvider()) {
            assert($testSuite instanceof TestSuiteForTestMethodWithDataProvider);

            $parameters['name'] = $testSuite->methodName();
        }

        $this->writeMessage('testSuiteFinished', $parameters);
    }

    public function testPrepared(Prepared $event): void
    {
        $test = $event->test();

        $parameters = [
            'name' => $test->name(),
        ];

        if ($test->isTestMethod()) {
            assert($test instanceof TestMethod);

            $parameters['locationHint'] = sprintf(
                'php_qn://%s::\\%s::%s',
                $test->file(),
                $test->className(),
                $test->methodName()
            );
        }

        $this->writeMessage('testStarted', $parameters);

        $this->test = $test;
        $this->time = $event->telemetryInfo()->time();
    }

    public function testAborted(Aborted $event): void
    {
        if ($this->test === null) {
            $this->test = $event->test();
            $this->time = $event->telemetryInfo()->time();
        }
    }

    public function testSkipped(Skipped $event): void
    {
        if ($this->test === null) {
            $this->test = $event->test();
            $this->time = $event->telemetryInfo()->time();
        }
    }

    public function testErrored(Errored $event): void
    {
        if ($this->test === null) {
            $this->test = $event->test();
            $this->time = $event->telemetryInfo()->time();
        }
    }

    public function testFailed(Failed $event): void
    {
        if ($this->test === null) {
            $this->test = $event->test();
            $this->time = $event->telemetryInfo()->time();
        }
    }

    public function testPassedWithWarning(PassedWithWarning $event): void
    {
        if ($this->test === null) {
            $this->test = $event->test();
            $this->time = $event->telemetryInfo()->time();
        }
    }

    public function testConsideredRisky(ConsideredRisky $event): void
    {
        if ($this->test === null) {
            $this->test = $event->test();
            $this->time = $event->telemetryInfo()->time();
        }
    }

    public function testFinished(Finished $event): void
    {
        $this->writeMessage(
            'testFinished',
            [
                'name'     => $event->test()->name(),
                'duration' => $this->duration($event),
            ]
        );

        $this->test = null;
        $this->time = null;
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(): void
    {
        Facade::registerSubscriber(new TestSuiteStartedSubscriber($this));
        Facade::registerSubscriber(new TestSuiteFinishedSubscriber($this));
        Facade::registerSubscriber(new TestPreparedSubscriber($this));
        Facade::registerSubscriber(new TestFinishedSubscriber($this));
        Facade::registerSubscriber(new TestPassedWithWarningSubscriber($this));
        Facade::registerSubscriber(new TestErroredSubscriber($this));
        Facade::registerSubscriber(new TestFailedSubscriber($this));
        Facade::registerSubscriber(new TestAbortedSubscriber($this));
        Facade::registerSubscriber(new TestSkippedSubscriber($this));
        Facade::registerSubscriber(new TestConsideredRiskySubscriber($this));
    }

    private function setFlowId(): void
    {
        if (stripos(ini_get('disable_functions'), 'getmypid') === false) {
            $this->flowId = getmypid();
        }
    }

    private function writeMessage(string $eventName, array $parameters = []): void
    {
        $this->write(
            sprintf(
                "\n##teamcity[%s",
                $eventName
            )
        );

        if ($this->flowId !== null) {
            $parameters['flowId'] = $this->flowId;
        }

        foreach ($parameters as $key => $value) {
            $this->write(
                sprintf(
                    " %s='%s'",
                    $key,
                    self::escape((string) $value)
                )
            );
        }

        $this->write("]\n");
    }

    /**
     * @throws InvalidArgumentException
     */
    private function duration(Event $event): int
    {
        if ($this->time === null) {
            return 0;
        }

        return (int) round($event->telemetryInfo()->time()->duration($this->time)->asFloat() * 1000);
    }

    private static function escape(string $string): string
    {
        return str_replace(
            ['|', "'", "\n", "\r", ']', '['],
            ['||', "|'", '|n', '|r', '|]', '|['],
            $string
        );
    }
}
