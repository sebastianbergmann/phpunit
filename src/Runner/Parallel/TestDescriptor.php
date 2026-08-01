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
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\RetryTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;

/**
 * One member of a test class work unit, in the form in which it travels to the
 * worker process that runs the unit.
 *
 * A member is a single test case or an aggregating suite whose members are
 * described recursively. The suites travel as suites so that the orchestration
 * they perform runs inside the worker, exactly as it would in a sequential
 * run: the DataProviderTestSuite of a data provider method emits the suite
 * event envelope that nests its tests in the logger output, the RetryTestSuite
 * of a retried test method builds and runs the additional attempts, and the
 * RepeatTestSuite of a repeated test method applies its failure threshold and
 * skips the remaining repetitions once it is exceeded.
 *
 * A descriptor holds only what the worker needs in order to rebuild the member:
 * the parent process describes a member with from(), and the worker turns the
 * descriptor back into a member with test(). Between the two, the descriptor
 * itself travels in the serialized worker command (see CommandStream), so
 * neither direction needs a representation of its own.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class TestDescriptor
{
    /**
     * Describe one member of a work unit. Runs in the parent process.
     *
     * @param class-string $className
     *
     * @throws WorkerException
     */
    final public static function from(Test $test, string $className): self
    {
        if ($test instanceof DataProviderTestSuite) {
            return DataProviderSuiteDescriptor::fromTestSuite($test, $className);
        }

        if ($test instanceof RetryTestSuite) {
            return RetrySuiteDescriptor::fromTestSuite($test, $className);
        }

        if ($test instanceof RepeatTestSuite) {
            return RepeatSuiteDescriptor::fromTestSuite($test, $className);
        }

        assert($test instanceof TestCase);

        return TestCaseDescriptor::fromTestCase($test, $className);
    }

    /**
     * Rebuild the member that this descriptor describes. Runs inside the
     * worker process.
     *
     * @param class-string<TestCase> $className
     */
    abstract public function test(string $className): Test;
}
