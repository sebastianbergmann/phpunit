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
use function hash_equals;
use function is_int;
use function property_exists;
use function sprintf;
use function strlen;
use function substr;
use function trim;
use function unserialize;
use PHPUnit\Event\Code\TestMethodBuilder;
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
use stdClass;
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
        if ($stderr !== '') {
            if (TestResultFacade::wasInterrupted()) {
                $this->testWasInterrupted($test);

                return;
            }

            $exception = new Exception(trim($stderr));

            assert($test instanceof TestCase);

            $test->setStatus(TestStatus::error($exception->getMessage()));

            $this->emitter->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception),
            );

            $this->emitter->testFinished(
                TestMethodBuilder::fromTestCase($test),
                0,
            );

            return;
        }

        if ($processResultNonce !== null && $serializedProcessResult !== '') {
            $nonceLength = strlen($processResultNonce);

            if (strlen($serializedProcessResult) < $nonceLength ||
                !hash_equals($processResultNonce, substr($serializedProcessResult, 0, $nonceLength))) {
                if (TestResultFacade::wasInterrupted()) {
                    $this->testWasInterrupted($test);

                    return;
                }

                $message = 'Test was run in child process and the result file was tampered with or written by an unexpected process';

                $this->emitter->childProcessErrored(ChildProcessReason::TestRequiringProcessIsolation, $message);

                $exception = new AssertionFailedError($message);

                assert($test instanceof TestCase);

                $test->setStatus(TestStatus::error($exception->getMessage()));

                $this->emitter->testErrored(
                    TestMethodBuilder::fromTestCase($test),
                    ThrowableBuilder::from($exception),
                );

                $this->emitter->testFinished(
                    TestMethodBuilder::fromTestCase($test),
                    0,
                );

                return;
            }

            $serializedProcessResult = substr($serializedProcessResult, $nonceLength);
        }

        try {
            $childResult = @unserialize($serializedProcessResult);
        } catch (Throwable $t) {
            // An object can only be unserialized when its class exists in this
            // process: an object of a test double class, which only exists in
            // the child process, cannot be assigned to a property whose type it
            // was declared to satisfy, for instance an object that a test
            // returned and that holds a test double.
            if (TestResultFacade::wasInterrupted()) {
                $this->testWasInterrupted($test);

                return;
            }

            $message = sprintf(
                'Test was run in child process and its result could not be unserialized: %s',
                $t->getMessage(),
            );

            $this->emitter->childProcessErrored(ChildProcessReason::TestRequiringProcessIsolation, $message);

            $exception = new AssertionFailedError($message);

            assert($test instanceof TestCase);

            $test->setStatus(TestStatus::error($exception->getMessage()));

            $this->emitter->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception),
            );

            $this->emitter->testFinished(
                TestMethodBuilder::fromTestCase($test),
                0,
            );

            return;
        }

        if (!$childResult instanceof stdClass ||
            !property_exists($childResult, 'events') ||
            !property_exists($childResult, 'passedTests') ||
            !property_exists($childResult, 'testResult') ||
            !property_exists($childResult, 'status') ||
            !property_exists($childResult, 'numAssertions') ||
            !$childResult->events instanceof EventCollection ||
            !$childResult->passedTests instanceof PassedTests ||
            !$childResult->status instanceof TestStatus ||
            !is_int($childResult->numAssertions) ||
            $childResult->numAssertions < 0) {
            if (TestResultFacade::wasInterrupted()) {
                $this->testWasInterrupted($test);

                return;
            }

            $message = 'Test was run in child process and ended unexpectedly';

            $this->emitter->childProcessErrored(ChildProcessReason::TestRequiringProcessIsolation, $message);

            $exception = new AssertionFailedError($message);

            assert($test instanceof TestCase);

            $test->setStatus(TestStatus::error($exception->getMessage()));

            $this->emitter->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception),
            );

            $this->emitter->testFinished(
                TestMethodBuilder::fromTestCase($test),
                0,
            );

            return;
        }

        $this->eventFacade->forward($this->withoutPreparationStarted($childResult->events));
        $this->passedTests->import($childResult->passedTests);

        assert($test instanceof TestCase);

        $test->setResult($childResult->testResult);
        $test->setStatus($childResult->status);
        $test->addToAssertionCount($childResult->numAssertions);

        if (!$this->codeCoverage->isActive()) {
            return;
        }

        if (!isset($childResult->codeCoverage) || !$childResult->codeCoverage instanceof \SebastianBergmann\CodeCoverage\CodeCoverage) {
            $this->emitter->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Code coverage could not be collected for %s',
                    TestMethodBuilder::fromTestCase($test)->id(),
                ),
            );

            return;
        }

        // @codeCoverageIgnoreStart
        CodeCoverage::instance()->codeCoverage()->merge(
            $childResult->codeCoverage,
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * When the test run was interrupted by a signal, the child process was
     * most likely terminated by the same signal before the test was finished,
     * so that it did not produce a usable result. The test is then reported
     * as skipped rather than as errored.
     */
    private function testWasInterrupted(Test $test): void
    {
        assert($test instanceof TestCase);

        $message = 'Test was run in child process and test execution was interrupted by a signal';

        $test->setStatus(TestStatus::skipped($message));

        $this->emitter->testSkipped(
            TestMethodBuilder::fromTestCase($test),
            $message,
        );

        $this->emitter->testFinished(
            TestMethodBuilder::fromTestCase($test),
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
