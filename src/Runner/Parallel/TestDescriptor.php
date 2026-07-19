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
use function base64_decode;
use function base64_encode;
use function count;
use function is_array;
use function is_int;
use function is_string;
use function serialize;
use function sprintf;
use function unserialize;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\RetryTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

/**
 * Encodes one member of a test class work unit as a descriptor that survives
 * the JSON-encoded worker command, and rebuilds the member from its descriptor
 * inside the worker process.
 *
 * A member is a single test case or an aggregating suite whose members are
 * encoded recursively. The suites travel as suites so that the orchestration
 * they perform runs inside the worker, exactly as it would in a sequential
 * run: the DataProviderTestSuite of a data provider method emits the suite
 * event envelope that nests its tests in the logger output, the RetryTestSuite
 * of a retried test method builds and runs the additional attempts, and the
 * RepeatTestSuite of a repeated test method applies its failure threshold and
 * skips the remaining repetitions once it is exceeded.
 *
 * Encoding and decoding live side by side in this class so that the two
 * directions of the worker protocol cannot drift apart.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestDescriptor
{
    /**
     * @param class-string $className
     *
     * @throws WorkerException
     *
     * @return array<string, mixed>
     */
    public static function encode(Test $test, string $className): array
    {
        if ($test instanceof DataProviderTestSuite) {
            $members = [];

            foreach ($test->tests() as $member) {
                $members[] = self::encode($member, $className);
            }

            return [
                'type'  => 'dataProvider',
                'name'  => $test->name(),
                'tests' => $members,
            ];
        }

        if ($test instanceof RetryTestSuite) {
            $aggregated = $test->tests();

            assert(count($aggregated) === 1 && $aggregated[0] instanceof TestCase);

            return [
                'type'        => 'retry',
                'name'        => $test->name(),
                'maxAttempts' => $test->maxAttempts(),
                'test'        => self::encodeTestCase($aggregated[0], $className),
            ];
        }

        if ($test instanceof RepeatTestSuite) {
            $repetitions = [];

            foreach ($test->tests() as $repetition) {
                assert($repetition instanceof TestCase);

                $repetitions[] = self::encodeTestCase($repetition, $className);
            }

            return [
                'type'             => 'repeat',
                'name'             => $test->name(),
                'failureThreshold' => $test->failureThreshold(),
                'tests'            => $repetitions,
            ];
        }

        assert($test instanceof TestCase);

        return self::encodeTestCase($test, $className);
    }

    /**
     * Rebuild a member from its descriptor. Runs inside the worker process.
     *
     * @param class-string<TestCase> $className
     */
    public static function decode(string $className, stdClass $descriptor): Test
    {
        $type = null;

        if (isset($descriptor->type) && is_string($descriptor->type)) {
            $type = $descriptor->type;
        }

        if ($type === 'dataProvider') {
            assert(is_string($descriptor->name) && $descriptor->name !== '');

            $suite = DataProviderTestSuite::empty($descriptor->name);

            assert(is_array($descriptor->tests));

            foreach ($descriptor->tests as $member) {
                assert($member instanceof stdClass);

                $suite->addTest(self::decode($className, $member));
            }

            return $suite;
        }

        if ($type === 'retry') {
            $testDescriptor = $descriptor->test;

            assert($testDescriptor instanceof stdClass);
            assert(is_string($descriptor->name) && $descriptor->name !== '');
            assert(is_int($descriptor->maxAttempts) && $descriptor->maxAttempts > 1);

            $factory = static function () use ($className, $testDescriptor): TestCase
            {
                return self::decodeTestCase($className, $testDescriptor);
            };

            return RetryTestSuite::fromTestCase(
                $descriptor->name,
                $factory(),
                $descriptor->maxAttempts,
                $factory,
            );
        }

        if ($type === 'repeat') {
            assert(is_string($descriptor->name) && $descriptor->name !== '');
            assert(is_int($descriptor->failureThreshold) && $descriptor->failureThreshold >= 1);

            $repetitions = [];

            assert(is_array($descriptor->tests));

            foreach ($descriptor->tests as $repetition) {
                assert($repetition instanceof stdClass);

                $repetitions[] = self::decodeTestCase($className, $repetition);
            }

            assert($repetitions !== []);

            return RepeatTestSuite::fromTests(
                $descriptor->name,
                $repetitions,
                $descriptor->failureThreshold,
            );
        }

        return self::decodeTestCase($className, $descriptor);
    }

    /**
     * @param class-string $className
     *
     * @throws WorkerException
     *
     * @return array<string, mixed>
     */
    private static function encodeTestCase(TestCase $test, string $className): array
    {
        try {
            $data            = base64_encode(serialize($test->providedData()));
            $dependencyInput = base64_encode(serialize($test->dependencyInput()));
        } catch (Throwable $t) {
            throw new WorkerException(
                sprintf(
                    'The tests of class %s cannot be run in parallel because their data cannot be serialized: %s',
                    $className,
                    $t->getMessage(),
                ),
            );
        }

        // The name of a data set is chosen by the data provider and is not
        // required to be valid UTF-8, which everything that travels in the
        // JSON-encoded command must be. A string name is therefore
        // base64-encoded for transport; an integer name needs no encoding.
        $dataName = $test->dataName();

        if (is_string($dataName)) {
            $dataName = base64_encode($dataName);
        }

        return [
            'type'             => 'test',
            'methodName'       => $test->name(),
            'data'             => $data,
            'dataName'         => $dataName,
            'dependencyInput'  => $dependencyInput,
            'repetition'       => $test->repetition(),
            'totalRepetitions' => $test->totalRepetitions(),
            'attempt'          => $test->attempt(),
            'maxAttempts'      => $test->maxAttempts(),
        ];
    }

    /**
     * @param class-string<TestCase> $className
     */
    private static function decodeTestCase(string $className, stdClass $descriptor): TestCase
    {
        assert(is_string($descriptor->methodName) && $descriptor->methodName !== '');
        assert(is_string($descriptor->data));
        assert(is_string($descriptor->dependencyInput));
        assert(is_int($descriptor->dataName) || is_string($descriptor->dataName));
        assert(is_int($descriptor->repetition) && $descriptor->repetition > 0);
        assert(is_int($descriptor->totalRepetitions) && $descriptor->totalRepetitions > 0);
        assert(is_int($descriptor->attempt) && $descriptor->attempt > 0);
        assert(is_int($descriptor->maxAttempts) && $descriptor->maxAttempts > 0);

        $test = new $className($descriptor->methodName);

        // A string data-set name travels base64-encoded because it is not
        // required to be valid UTF-8; an integer name travels as-is.
        $dataName = $descriptor->dataName;

        if (is_string($dataName)) {
            $decodedDataName = base64_decode($dataName, true);

            assert($decodedDataName !== false);

            $dataName = $decodedDataName;
        }

        $decodedData            = base64_decode($descriptor->data, true);
        $decodedDependencyInput = base64_decode($descriptor->dependencyInput, true);

        assert($decodedData !== false);
        assert($decodedDependencyInput !== false);

        $providedData    = unserialize($decodedData);
        $dependencyInput = unserialize($decodedDependencyInput);

        assert(is_array($providedData));
        assert(is_array($dependencyInput));

        /** @var array<string, mixed> $dependencyInput */
        $test->setData($dataName, $providedData);
        $test->setDependencyInput($dependencyInput);
        $test->setRepetition($descriptor->repetition, $descriptor->totalRepetitions);
        $test->setAttempt($descriptor->attempt, $descriptor->maxAttempts);

        return $test;
    }
}
