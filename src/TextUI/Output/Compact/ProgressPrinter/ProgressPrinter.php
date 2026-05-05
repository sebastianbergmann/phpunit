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
use function str_starts_with;
use function strlen;
use function substr;
use function trim;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\Test\AfterLastTestMethodFailed;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodFailed;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\TextUI\Output\Compact\Renderer;
use PHPUnit\TextUI\Output\Printer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ProgressPrinter
{
    private Printer $printer;
    private Renderer $renderer;

    public function __construct(Printer $printer, Facade $facade)
    {
        $this->printer  = $printer;
        $this->renderer = new Renderer($printer);

        $this->registerSubscribers($facade);
    }

    public function testErrored(Errored $event): void
    {
        $this->printError($this->renderer->nameOfTest($event->test()), $event->throwable());
    }

    public function testFailed(Failed $event): void
    {
        $this->printFailure($this->renderer->nameOfTest($event->test()), $event->throwable());
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

    private function printError(string $title, Throwable $throwable): void
    {
        $this->printer->print(PHP_EOL . '--- ERROR: ' . $title . PHP_EOL);
        $this->renderer->printThrowable($throwable);
    }

    private function printFailure(string $title, Throwable $throwable): void
    {
        $this->printer->print(PHP_EOL . '--- FAILURE: ' . $title . PHP_EOL);

        $body = $throwable->description();

        if (str_starts_with($body, 'AssertionError: ')) {
            $body = substr($body, strlen('AssertionError: '));
        }

        $this->printer->print(trim($body) . PHP_EOL);
        $this->renderer->printStackTrace($throwable->stackTrace());
    }

    private function registerSubscribers(Facade $facade): void
    {
        $facade->registerSubscribers(
            new TestErroredSubscriber($this),
            new TestFailedSubscriber($this),
            new BeforeFirstTestMethodErroredSubscriber($this),
            new BeforeFirstTestMethodFailedSubscriber($this),
            new AfterLastTestMethodErroredSubscriber($this),
            new AfterLastTestMethodFailedSubscriber($this),
        );
    }
}
