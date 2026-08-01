<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function assert;
use function count;
use PHPUnit\Framework\RetryTestSuite;
use PHPUnit\Framework\TestCase;

/**
 * The descriptor of the suite that aggregates the attempts of a retried test
 * method. Only the first attempt is described; the suite travels to the worker
 * as a suite so that the additional attempts are built and run inside the
 * worker, from the same descriptor.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RetrySuiteDescriptor extends TestDescriptor
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var positive-int
     */
    private int $maxAttempts;
    private TestCaseDescriptor $testCase;

    /**
     * @param class-string $className
     *
     * @throws WorkerException
     */
    public static function fromTestSuite(RetryTestSuite $suite, string $className): self
    {
        $aggregated = $suite->tests();

        assert(count($aggregated) === 1 && $aggregated[0] instanceof TestCase);

        return new self(
            $suite->name(),
            $suite->maxAttempts(),
            TestCaseDescriptor::fromTestCase($aggregated[0], $className),
        );
    }

    /**
     * @param non-empty-string $name
     * @param positive-int     $maxAttempts
     */
    private function __construct(string $name, int $maxAttempts, TestCaseDescriptor $testCase)
    {
        $this->name        = $name;
        $this->maxAttempts = $maxAttempts;
        $this->testCase    = $testCase;
    }

    /**
     * @param class-string<TestCase> $className
     */
    public function test(string $className): RetryTestSuite
    {
        $testCase = $this->testCase;

        $factory = static function () use ($className, $testCase): TestCase
        {
            return $testCase->test($className);
        };

        return RetryTestSuite::fromTestCase(
            $this->name,
            $factory(),
            $this->maxAttempts,
            $factory,
        );
    }
}
