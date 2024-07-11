<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\Tap;

use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\TextUI\Output\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TapLogger
{
    private readonly Printer $printer;

    /**
     * @var non-negative-int
     */
    private int $numberOfTests = 0;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct(Printer $printer, Facade $facade)
    {
        $this->printer = $printer;

        $this->registerSubscribers($facade);
    }

    public function executionStarted(): void
    {
        $this->printer->print('TAP version 14' . PHP_EOL);
    }

    public function executionFinished(): void
    {
        $this->printer->print('1..' . $this->numberOfTests . PHP_EOL);

        $this->printer->flush();
    }

    public function testPreparationStarted(PreparationStarted $event): void
    {
        $this->numberOfTests++;
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(Facade $facade): void
    {
        $facade->registerSubscribers(
            new TestRunnerExecutionStartedSubscriber($this),
            new TestRunnerExecutionFinishedSubscriber($this),
            new TestPreparationStartedSubscriber($this),
        );
    }
}
