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

use function array_pop;
use function array_reverse;
use function assert;
use PHPUnit\Event;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RepeatTestSuite extends TestSuite
{
    /**
     * @var positive-int
     */
    private int $failureThreshold = 1;
    private bool $wasRun          = false;

    /**
     * @param non-empty-string         $name
     * @param non-empty-list<TestCase> $tests
     * @param positive-int             $failureThreshold
     * @param list<non-empty-string>   $groups
     */
    public static function fromTests(string $name, array $tests, int $failureThreshold, array $groups = []): self
    {
        $suite = self::empty($name);

        $suite->failureThreshold = $failureThreshold;

        foreach ($tests as $test) {
            $suite->addTest($test, $groups);
        }

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

        $tests = array_reverse($tests);

        $this->setTests([]);

        $failureCount         = 0;
        $lastFailedRepetition = 0;

        while (($test = array_pop($tests)) !== null) {
            if (TestResultFacade::shouldStop()) {
                $emitter->testRunnerExecutionAborted();

                break;
            }

            if ($failureCount >= $this->failureThreshold) {
                $test->markSkippedForRepeatAbort($lastFailedRepetition);

                continue;
            }

            $test->run();

            if ($test->status()->isFailure() || $test->status()->isError()) {
                $failureCount++;
                $lastFailedRepetition = $test->repetition();
            }
        }

        $emitter->testSuiteFinished($testSuiteValueObjectForEvents);
    }

    /**
     * @param list<ExecutionOrderDependency> $dependencies
     */
    public function setDependencies(array $dependencies): void
    {
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
}
