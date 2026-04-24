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

use function count;
use PHPUnit\Event;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\NoPreviousThrowableException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RepeatTestSuite implements Reorderable, SelfDescribing, Test
{
    /**
     * @var non-empty-list<TestCase>
     */
    private array $tests;

    /**
     * @var positive-int
     */
    private int $failureThreshold;

    /**
     * @param non-empty-list<TestCase> $tests
     * @param positive-int             $failureThreshold
     */
    public function __construct(array $tests, int $failureThreshold = 1)
    {
        $this->tests            = $tests;
        $this->failureThreshold = $failureThreshold;
    }

    public function count(): int
    {
        return count($this->tests);
    }

    /**
     * @throws Event\InvalidArgumentException
     * @throws Exception
     * @throws NoPreviousThrowableException
     */
    public function run(): void
    {
        $failureCount         = 0;
        $lastFailedRepetition = 0;

        foreach ($this->tests as $test) {
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
    }

    public function sortId(): string
    {
        return $this->tests[0]->sortId();
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    public function provides(): array
    {
        return $this->tests[0]->provides();
    }

    /**
     * @return list<ExecutionOrderDependency>
     */
    public function requires(): array
    {
        return $this->tests[0]->requires();
    }

    /**
     * @param list<ExecutionOrderDependency> $dependencies
     */
    public function setDependencies(array $dependencies): void
    {
        foreach ($this->tests as $test) {
            $test->setDependencies($dependencies);
        }
    }

    public function name(): string
    {
        return $this->tests[0]::class . '::' . $this->tests[0]->nameWithDataSet();
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->name();
    }

    public function valueObjectForEvents(): TestMethod
    {
        return $this->tests[0]->valueObjectForEvents();
    }
}
