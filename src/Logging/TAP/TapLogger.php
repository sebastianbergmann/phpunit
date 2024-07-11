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

use function preg_split;
use function rtrim;
use function sprintf;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PreparationFailed;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
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
    private int $numberOfTests      = 0;
    private bool $yamlHeaderPrinted = false;

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
        $this->printer->print(PHP_EOL . '1..' . $this->numberOfTests . PHP_EOL);

        $this->printer->flush();
    }

    public function testPreparationStarted(PreparationStarted $event): void
    {
        $this->numberOfTests++;
    }

    public function testPrepared(Prepared $event): void
    {
        $this->printer->print(
            sprintf(
                PHP_EOL . '# successfully prepared %s' . PHP_EOL,
                $event->test()->id(),
            ),
        );
    }

    public function testPreparationFailed(PreparationFailed $event): void
    {
        $this->printer->print(
            sprintf(
                PHP_EOL . '# failed to prepare %s' . PHP_EOL,
                $event->test()->id(),
            ),
        );
    }

    public function testPassed(Passed $event): void
    {
        $this->printer->print(
            sprintf(
                'ok %d - %s' . PHP_EOL,
                $this->numberOfTests,
                $event->test()->id(),
            ),
        );
    }

    public function testErrored(Errored $event): void
    {
        $this->printer->print(
            sprintf(
                'not ok %d - %s' . PHP_EOL,
                $this->numberOfTests,
                $event->test()->id(),
            ),
        );

        $this->printThrowable($event->throwable(), 'error');
    }

    public function testFailed(Failed $event): void
    {
        $this->printer->print(
            sprintf(
                'not ok %d - %s' . PHP_EOL,
                $this->numberOfTests,
                $event->test()->id(),
            ),
        );

        $this->printThrowable($event->throwable(), 'failure');
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
            new TestPreparedSubscriber($this),
            new TestPreparationFailedSubscriber($this),
            new TestErroredSubscriber($this),
            new TestFailedSubscriber($this),
            new TestPassedSubscriber($this),
        );
    }

    private function printThrowable(Throwable $throwable, string $severity): void
    {
        $this->printYamlHeader();

        $this->printer->print('  severity: ' . $severity . PHP_EOL);

        $this->printer->print('  message: |' . PHP_EOL);
        $this->printer->print($this->indentMultilineString($throwable->message()) . PHP_EOL);

        $this->printer->print('  stackTrace: |' . PHP_EOL);
        $this->printer->print($this->indentMultilineString(rtrim($throwable->stackTrace())));

        $this->printYamlFooter();
    }

    private function printYamlHeader(): void
    {
        if ($this->yamlHeaderPrinted) {
            return;
        }

        $this->printer->print('  ---' . PHP_EOL);

        $this->yamlHeaderPrinted = true;
    }

    private function printYamlFooter(): void
    {
        if (!$this->yamlHeaderPrinted) {
            return;
        }

        $this->printer->print('  ...' . PHP_EOL);

        $this->yamlHeaderPrinted = false;
    }

    private function indentMultilineString(string $string): string
    {
        $result = '';

        foreach (preg_split('/[\r\n]+/', $string) as $line) {
            $result .= '    ' . rtrim($line) . PHP_EOL;
        }

        return $result;
    }
}
