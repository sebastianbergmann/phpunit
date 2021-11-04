<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\ResultPrinter\Standard;

use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Aborted;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\ResultPrinter\ResultPrinter as ResultPrinterInterface;
use PHPUnit\Util\Printer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter extends Printer implements ResultPrinterInterface
{
    public function __construct(string $out)
    {
        parent::__construct($out);

        $this->registerSubscribers();
    }

    public function printResult(TestResult $result): void
    {
    }

    public function write(string $buffer): void
    {
    }

    public function testAborted(Aborted $event): void
    {
    }

    public function testConsideredRisky(ConsideredRisky $event): void
    {
    }

    public function testErrored(Errored $event): void
    {
    }

    public function testFailed(Failed $event): void
    {
    }

    public function testFinished(Finished $event): void
    {
    }

    public function testPassedWithWarning(PassedWithWarning $event): void
    {
    }

    public function testSkipped(Skipped $event): void
    {
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(): void
    {
        Facade::registerSubscriber(new TestFinishedSubscriber($this));
        Facade::registerSubscriber(new TestConsideredRiskySubscriber($this));
        Facade::registerSubscriber(new TestPassedWithWarningSubscriber($this));
        Facade::registerSubscriber(new TestErroredSubscriber($this));
        Facade::registerSubscriber(new TestFailedSubscriber($this));
        Facade::registerSubscriber(new TestAbortedSubscriber($this));
        Facade::registerSubscriber(new TestSkippedSubscriber($this));
    }
}
