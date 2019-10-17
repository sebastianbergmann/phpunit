<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

final class Emitter
{
    private Dispatcher $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function executionWasStarted(): void
    {
        $this->dispatcher->dispatch(new Execution\BeforeExecution());
    }

    public function runWasStarted(): void
    {
        $this->dispatcher->dispatch(new Run\BeforeRun(new Run\Run()));
    }

    public function runWasCompleted(): void
    {
        $this->dispatcher->dispatch(new Run\AfterRun(new Run\Run()));
    }

    public function testWasStarted(): void
    {
        $this->dispatcher->dispatch(new Test\BeforeTest(new Test\Test()));
    }

    public function testSuiteWasStarted(string $name, int $numberOfTests): void
    {
        $this->dispatcher->dispatch(new TestSuite\BeforeTestSuite(new TestSuite\TestSuite(
            $name,
            $numberOfTests
        )));
    }

    public function testSuiteWasFinished(string $name, int $numberOfTests): void
    {
        $this->dispatcher->dispatch(new TestSuite\AfterTestSuite(new TestSuite\TestSuite(
            $name,
            $numberOfTests
        )));
    }

    public function firstTestWasStarted(): void
    {
        $this->dispatcher->dispatch(new Test\BeforeFirstTest());
    }

    public function lastTestWasCompleted(): void
    {
        $this->dispatcher->dispatch(new Test\AfterLastTest());
    }

    public function testWasCompletedWithError(): void
    {
        $this->dispatcher->dispatch(new Test\AfterTest(
            new Test\Test(),
            new Test\Result\Error()
        ));
    }

    public function testWasCompletedWithFailure(): void
    {
        $this->dispatcher->dispatch(new Test\AfterTest(
            new Test\Test(),
            new Test\Result\Failure()
        ));
    }

    public function testWasCompletedWithResultThatNeedsClarification(): void
    {
        $this->dispatcher->dispatch(new Test\AfterTest(
            new Test\Test(),
            new Test\Result\NeedsClarification()
        ));
    }

    public function testWasCompletedWithWarning(): void
    {
        $this->dispatcher->dispatch(new Test\AfterTest(
            new Test\Test(),
            new Test\Result\Warning()
        ));
    }

    public function testWasSkipped(): void
    {
        $this->dispatcher->dispatch(new Test\AfterTest(
            new Test\Test(),
            new Test\Result\Skipped()
        ));
    }
}
