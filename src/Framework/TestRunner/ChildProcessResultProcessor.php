<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use function assert;
use function is_int;
use function property_exists;
use function sprintf;
use function trim;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TestRunner\TestResult\PassedTests;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ChildProcessResultProcessor
{
    private Facade $eventFacade;
    private Emitter $emitter;
    private PassedTests $passedTests;
    private CodeCoverage $codeCoverage;

    public function __construct(Facade $eventFacade, Emitter $emitter, PassedTests $passedTests, CodeCoverage $codeCoverage)
    {
        $this->eventFacade  = $eventFacade;
        $this->emitter      = $emitter;
        $this->passedTests  = $passedTests;
        $this->codeCoverage = $codeCoverage;
    }

    /**
     * @param ?non-empty-string $processResultNonce
     */
    public function process(Test $test, string $serializedProcessResult, string $stderr, ?string $processResultNonce = null): void
    {
        assert($test instanceof TestCase);

        if ($stderr !== '') {
            $this->testErrored($test, new Exception(trim($stderr)));

            return;
        }

        $verifiedProcessResult = ChildProcessResultEnvelope::verifyAndStripNonce($serializedProcessResult, $processResultNonce);

        if ($verifiedProcessResult === null) {
            $this->childProcessErrored(
                $test,
                'Test was run in child process and the result file was tampered with or written by an unexpected process',
            );

            return;
        }

        try {
            $childResult = ChildProcessResultEnvelope::decode($verifiedProcessResult);
        } catch (Throwable $t) {
            // An object can only be unserialized when its class exists in this
            // process: an object of a test double class, which only exists in
            // the child process, cannot be assigned to a property whose type it
            // was declared to satisfy, for instance an object that a test
            // returned and that holds a test double.
            $this->childProcessErrored(
                $test,
                sprintf(
                    'Test was run in child process and its result could not be unserialized: %s',
                    $t->getMessage(),
                ),
            );

            return;
        }

        if ($childResult === null ||
            !property_exists($childResult, 'testResult') ||
            !property_exists($childResult, 'status') ||
            !property_exists($childResult, 'numAssertions') ||
            !$childResult->status instanceof TestStatus ||
            !is_int($childResult->numAssertions) ||
            $childResult->numAssertions < 0) {
            $this->childProcessErrored(
                $test,
                'Test was run in child process and ended unexpectedly',
            );

            return;
        }

        assert($childResult->events instanceof EventCollection);
        assert($childResult->passedTests instanceof PassedTests);

        $this->eventFacade->forward($this->withoutPreparationStarted($childResult->events));
        $this->passedTests->import($childResult->passedTests);

        $test->setResult($childResult->testResult);
        $test->setStatus($childResult->status);
        $test->addToAssertionCount($childResult->numAssertions);

        if (!ChildProcessResultEnvelope::mergeCodeCoverage($childResult, $this->codeCoverage)) {
            $this->emitter->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Code coverage could not be collected for %s',
                    $test->valueObjectForEvents()->id(),
                ),
            );
        }
    }

    /**
     * @param non-empty-string $message
     */
    private function childProcessErrored(TestCase $test, string $message): void
    {
        if (TestResultFacade::wasInterrupted()) {
            $this->testWasInterrupted($test);

            return;
        }

        $this->emitter->childProcessErrored(ChildProcessReason::TestRequiringProcessIsolation, $message);

        $this->testErrored($test, new AssertionFailedError($message));
    }

    private function testErrored(TestCase $test, Throwable $exception): void
    {
        if (TestResultFacade::wasInterrupted()) {
            $this->testWasInterrupted($test);

            return;
        }

        $test->setStatus(TestStatus::error($exception->getMessage()));

        $valueObjectForEvents = $test->valueObjectForEvents();

        $this->emitter->testErrored(
            $valueObjectForEvents,
            ThrowableBuilder::from($exception),
        );

        $this->emitter->testFinished(
            $valueObjectForEvents,
            0,
        );
    }

    /**
     * When the test run was interrupted by a signal, the child process was
     * most likely terminated by the same signal before the test was finished,
     * so that it did not produce a usable result. The test is then reported
     * as skipped rather than as errored.
     */
    private function testWasInterrupted(TestCase $test): void
    {
        $message = 'Test was run in child process and test execution was interrupted by a signal';

        $test->setStatus(TestStatus::skipped($message));

        $valueObjectForEvents = $test->valueObjectForEvents();

        $this->emitter->testSkipped(
            $valueObjectForEvents,
            $message,
        );

        $this->emitter->testFinished(
            $valueObjectForEvents,
            0,
        );
    }

    /**
     * The test was started in this process, before the child process was
     * started, so that the events that are emitted for it in this process
     * happen between its start and its end. The child process starts the
     * test, too, for the subscribers that run in the child process.
     */
    private function withoutPreparationStarted(EventCollection $events): EventCollection
    {
        $result = new EventCollection;

        foreach ($events as $event) {
            if ($event instanceof PreparationStarted) {
                continue;
            }

            $result->add($event);
        }

        return $result;
    }
}
