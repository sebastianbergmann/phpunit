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
use function trim;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TestRunner\TestResult\PassedTests;

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
        if (TestResultFacade::wasInterrupted()) {
            assert($test instanceof TestCase);

            $this->emitter->testFinished(
                TestMethodBuilder::fromTestCase($test),
                0,
            );

            return;
        }

        if ($stderr !== '') {
            $exception = new Exception(trim($stderr));

            assert($test instanceof TestCase);

            $test->setStatus(TestStatus::error($exception->getMessage()));

            $this->emitter->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception),
            );

            return;
        }

        $verifiedProcessResult = ChildProcessResultEnvelope::verifyAndStripNonce($serializedProcessResult, $processResultNonce);

        if ($verifiedProcessResult === null) {
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

        $childResult = ChildProcessResultEnvelope::decode($verifiedProcessResult);

        if ($childResult === null ||
            !property_exists($childResult, 'testResult') ||
            !property_exists($childResult, 'status') ||
            !property_exists($childResult, 'numAssertions') ||
            !$childResult->status instanceof TestStatus ||
            !is_int($childResult->numAssertions) ||
            $childResult->numAssertions < 0) {
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

        assert($childResult->events instanceof EventCollection);
        assert($childResult->passedTests instanceof PassedTests);

        $this->eventFacade->forward($childResult->events);
        $this->passedTests->import($childResult->passedTests);

        assert($test instanceof TestCase);

        $test->setResult($childResult->testResult);
        $test->setStatus($childResult->status);
        $test->addToAssertionCount($childResult->numAssertions);

        ChildProcessResultEnvelope::mergeCodeCoverage($childResult, $this->codeCoverage);
    }
}
