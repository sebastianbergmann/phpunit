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
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\TestCase;

/**
 * The descriptor of the suite that aggregates the repetitions of a repeated
 * test method. All repetitions are described; the suite travels to the worker
 * as a suite so that its failure threshold is applied inside the worker, which
 * skips the remaining repetitions once the threshold is exceeded.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RepeatSuiteDescriptor extends TestDescriptor
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var positive-int
     */
    private int $failureThreshold;

    /**
     * @var non-empty-list<TestCaseDescriptor>
     */
    private array $repetitions;

    /**
     * @param class-string $className
     *
     * @throws WorkerException
     */
    public static function fromTestSuite(RepeatTestSuite $suite, string $className): self
    {
        $repetitions = [];

        foreach ($suite->tests() as $repetition) {
            assert($repetition instanceof TestCase);

            $repetitions[] = TestCaseDescriptor::fromTestCase($repetition, $className);
        }

        assert($repetitions !== []);

        return new self($suite->name(), $suite->failureThreshold(), $repetitions);
    }

    /**
     * @param non-empty-string                   $name
     * @param positive-int                       $failureThreshold
     * @param non-empty-list<TestCaseDescriptor> $repetitions
     */
    private function __construct(string $name, int $failureThreshold, array $repetitions)
    {
        $this->name             = $name;
        $this->failureThreshold = $failureThreshold;
        $this->repetitions      = $repetitions;
    }

    /**
     * @param class-string<TestCase> $className
     */
    public function test(string $className): RepeatTestSuite
    {
        $repetitions = [];

        foreach ($this->repetitions as $repetition) {
            $repetitions[] = $repetition->test($className);
        }

        return RepeatTestSuite::fromTests(
            $this->name,
            $repetitions,
            $this->failureThreshold,
        );
    }
}
