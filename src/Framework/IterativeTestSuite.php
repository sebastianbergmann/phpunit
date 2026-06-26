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
use PHPUnit\Event;
use PHPUnit\Event\NoPreviousThrowableException;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;

/**
 * A test suite that aggregates the executions of a single test method, such as
 * the repetitions of a repeated test or the attempts of a retried test.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class IterativeTestSuite extends TestSuite
{
    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $dependencies = [];
    private bool $wasRun        = false;

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

        /** @var list<Test> $tests */
        $tests = [];

        foreach ($this as $test) {
            $tests[] = $test;
        }

        $this->setTests([]);

        $this->execute($tests, $emitter);

        $emitter->testSuiteFinished($testSuiteValueObjectForEvents);
    }

    /**
     * @param list<ExecutionOrderDependency> $dependencies
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;

        foreach ($this->tests() as $test) {
            if (!$test instanceof TestCase) {
                continue;
            }

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

        assert($tests[0] instanceof Reorderable);

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

        assert($tests[0] instanceof Reorderable);

        return $tests[0]->requires();
    }

    public function sortId(): string
    {
        $tests = $this->tests();

        assert($tests !== []);
        assert($tests[0] instanceof Reorderable);

        return $tests[0]->sortId();
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    final protected function dependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param list<Test> $tests
     *
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     */
    abstract protected function execute(array $tests, Event\Emitter $emitter): void;
}
