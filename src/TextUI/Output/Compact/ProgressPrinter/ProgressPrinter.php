<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Compact\ProgressPrinter;

use const PHP_EOL;
use function assert;
use function rtrim;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\Test\AfterLastTestMethodFailed;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodFailed;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PrintedUnexpectedOutput;
use PHPUnit\TextUI\Output\Compact\Renderer;
use PHPUnit\TextUI\Output\Printer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ProgressPrinter
{
    private readonly Printer $printer;
    private readonly Renderer $renderer;
    private readonly bool $displayUnexpectedOutput;
    private ?Test $currentTest      = null;
    private bool $hasPrintedRecords = false;

    public function __construct(Printer $printer, Facade $facade, bool $displayUnexpectedOutput)
    {
        $this->printer                 = $printer;
        $this->renderer                = new Renderer($printer);
        $this->displayUnexpectedOutput = $displayUnexpectedOutput;

        $this->registerSubscribers($facade);
    }

    public function testPreparationStarted(PreparationStarted $event): void
    {
        $this->currentTest = $event->test();
    }

    public function testErrored(Errored $event): void
    {
        $this->printError($this->renderer->nameOfTest($event->test()), $event->throwable());
    }

    public function testFailed(Failed $event): void
    {
        $this->printFailure($this->renderer->nameOfTest($event->test()), $event->throwable());
    }

    public function testPrintedUnexpectedOutput(PrintedUnexpectedOutput $event): void
    {
        if (!$this->displayUnexpectedOutput) {
            return;
        }

        assert($this->currentTest !== null);

        $this->renderer->printHeader('OUTPUT', $this->renderer->nameOfTest($this->currentTest));

        $output = rtrim($event->output(), "\r\n");

        if ($output !== '') {
            $this->renderer->printBody($output);
        }

        $this->hasPrintedRecords = true;
    }

    public function beforeFirstTestMethodErrored(BeforeFirstTestMethodErrored $event): void
    {
        $this->printError($event->testClassName(), $event->throwable());
    }

    public function beforeFirstTestMethodFailed(BeforeFirstTestMethodFailed $event): void
    {
        $this->printFailure($event->testClassName(), $event->throwable());
    }

    public function afterLastTestMethodErrored(AfterLastTestMethodErrored $event): void
    {
        $this->printError($event->testClassName(), $event->throwable());
    }

    public function afterLastTestMethodFailed(AfterLastTestMethodFailed $event): void
    {
        $this->printFailure($event->testClassName(), $event->throwable());
    }

    /**
     * Separates the records printed during the run from the summary that follows.
     */
    public function testRunnerExecutionFinished(): void
    {
        if ($this->hasPrintedRecords) {
            $this->printer->print(PHP_EOL);
        }
    }

    private function printError(string $title, Throwable $throwable): void
    {
        $this->renderer->printHeader('ERROR', $title);
        $this->renderer->printThrowable($throwable);

        $this->hasPrintedRecords = true;
    }

    private function printFailure(string $title, Throwable $throwable): void
    {
        $this->renderer->printHeader('FAILURE', $title);

        $body = $throwable->description();

        if (str_starts_with($body, 'AssertionError: ')) {
            $body = substr($body, strlen('AssertionError: '));
        }

        $this->renderer->printBody(trim($body));
        $this->renderer->printStackTrace($throwable->stackTrace());

        $this->hasPrintedRecords = true;
    }

    private function registerSubscribers(Facade $facade): void
    {
        $facade->registerSubscribers(
            new TestPreparationStartedSubscriber($this),
            new TestErroredSubscriber($this),
            new TestFailedSubscriber($this),
            new TestPrintedUnexpectedOutputSubscriber($this),
            new BeforeFirstTestMethodErroredSubscriber($this),
            new BeforeFirstTestMethodFailedSubscriber($this),
            new AfterLastTestMethodErroredSubscriber($this),
            new AfterLastTestMethodFailedSubscriber($this),
            new TestRunnerExecutionFinishedSubscriber($this),
        );
    }
}
