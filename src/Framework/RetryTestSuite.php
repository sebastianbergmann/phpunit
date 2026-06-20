<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function assert;
use Closure;
use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RetryTestSuite extends TestSuite
{
    /**
     * @var positive-int
     */
    private int $maxAttempts = 1;

    /**
     * @var ?Closure(): TestCase
     */
    private ?Closure $additionalAttemptFactory = null;

    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $dependencies = [];
    private bool $wasRun        = false;

    /**
     * @param non-empty-string       $name
     * @param positive-int           $maxAttempts
     * @param Closure(): TestCase    $additionalAttemptFactory
     * @param list<non-empty-string> $groups
     */
    public static function fromTestCase(string $name, TestCase $test, int $maxAttempts, Closure $additionalAttemptFactory, array $groups = []): self
    {
        $suite = self::empty($name);

        $suite->maxAttempts              = $maxAttempts;
        $suite->additionalAttemptFactory = $additionalAttemptFactory;

        $suite->addTest($test, $groups);

        return $suite;
    }

    /**
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     */
    public function run(): void
    {
        if ($this->wasRun) {
            // @codeCoverageIgnoreStart
            throw new Exception('The tests aggregated by this TestSuite were already run');
            // @codeCoverageIgnoreEnd
        }

        $this->wasRun = true;

        if ($this->isEmpty()) {
            return;
        }

        $emitter                       = Event\Facade::emitter();
        $testSuiteValueObjectForEvents = Event\TestSuite\TestSuiteBuilder::from($this);

        $emitter->testSuiteStarted($testSuiteValueObjectForEvents);

        /** @var list<TestCase> $tests */
        $tests = [];

        foreach ($this as $test) {
            assert($test instanceof TestCase);

            $tests[] = $test;
        }

        $this->setTests([]);

        if ($tests !== []) {
            $this->runAttempts($tests[0], $emitter);
        }

        $emitter->testSuiteFinished($testSuiteValueObjectForEvents);
    }

    /**
     * @return positive-int
     */
    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * @param list<ExecutionOrderDependency> $dependencies
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;

        foreach ($this->tests() as $test) {
            assert($test instanceof TestCase);

            $test->setDependencies($dependencies);
        }
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    public function provides(): array
    {
        $tests = $this->tests();

        if ($tests === []) {
            return [];
        }

        assert($tests[0] instanceof TestCase);

        return $tests[0]->provides();
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    public function requires(): array
    {
        $tests = $this->tests();

        if ($tests === []) {
            return [];
        }

        assert($tests[0] instanceof TestCase);

        return $tests[0]->requires();
    }

    public function sortId(): string
    {
        $tests = $this->tests();

        assert($tests !== []);
        assert($tests[0] instanceof TestCase);

        return $tests[0]->sortId();
    }

    /**
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     */
    private function runAttempts(TestCase $test, Event\Emitter $emitter): void
    {
        $facade = Event\Facade::instance();

        for ($attempt = 1; $attempt <= $this->maxAttempts; $attempt++) {
            if (TestResultFacade::shouldStop()) {
                $emitter->testRunnerExecutionAborted();

                return;
            }

            if ($attempt > 1) {
                assert($this->additionalAttemptFactory !== null);

                $test = ($this->additionalAttemptFactory)();

                $test->setDependencies($this->dependencies);
            }

            $test->setAttempt($attempt, $this->maxAttempts);

            $facade->startCollectingEvents();

            $test->run();

            $events = $facade->stopCollectingEvents();

            $retryable = ($test->status()->isFailure() || $test->status()->isError()) &&
                         $attempt < $this->maxAttempts;

            if (!$retryable) {
                $facade->forward($events);

                return;
            }

            if (!$this->emitAttemptEvent($events, $emitter)) {
                // the attempt's outcome could not be determined from its events,
                // forward them unchanged rather than discard information
                $facade->forward($events);

                return;
            }
        }
    }

    private function emitAttemptEvent(EventCollection $events, Event\Emitter $emitter): bool
    {
        foreach ($events as $event) {
            if ($event instanceof Event\Test\Failed) {
                $comparisonFailure = null;

                if ($event->hasComparisonFailure()) {
                    $comparisonFailure = $event->comparisonFailure();
                }

                $emitter->testAttemptFailed(
                    $event->test(),
                    $event->throwable(),
                    $comparisonFailure,
                );

                return true;
            }

            if ($event instanceof Event\Test\Errored) {
                $emitter->testAttemptErrored(
                    $event->test(),
                    $event->throwable(),
                );

                return true;
            }
        }

        return false;
    }
}
