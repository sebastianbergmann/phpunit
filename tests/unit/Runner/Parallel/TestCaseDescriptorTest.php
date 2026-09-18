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

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use stdClass;

#[CoversClass(TestCaseDescriptor::class)]
#[Small]
final class TestCaseDescriptorTest extends TestCase
{
    public function testRebuildsTheTestMethodItDescribes(): void
    {
        $test = $this->rebuild(new WorkerFirstTest('testStartsTheProcessLocalCounter'));

        $this->assertInstanceOf(WorkerFirstTest::class, $test);
        $this->assertSame('testStartsTheProcessLocalCounter', $test->name());
    }

    public function testRebuildsTheDataThatTheDataProviderProvided(): void
    {
        $described = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $described->setData('the data set', ['first', 'second']);

        $test = $this->rebuild($described);

        $this->assertSame(['first', 'second'], $test->providedData());
        $this->assertSame('the data set', $test->dataName());
    }

    public function testRebuildsTheDataOfATestCaseThatADataProviderKeyedByNumber(): void
    {
        $described = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $described->setData(1, ['second data set']);

        $test = $this->rebuild($described);

        $this->assertSame(['second data set'], $test->providedData());
        $this->assertSame(1, $test->dataName());
    }

    public function testRebuildsTheInputProvidedByTheTestsTheTestCaseDependsOn(): void
    {
        $described = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $described->setDependencyInput(['WorkerFirstTest::testThatIsDependedUpon' => 'its return value']);

        $test = $this->rebuild($described);

        $this->assertSame(['WorkerFirstTest::testThatIsDependedUpon' => 'its return value'], $test->dependencyInput());
    }

    public function testRebuildsTheObjectsCarriedByTheDataRatherThanSharingThem(): void
    {
        $object = new stdClass;

        $object->value = 'provided by the data provider';

        $described = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $described->setData(0, [$object]);

        $rebuilt = $this->rebuild($described)->providedData();

        // The data is serialized while the descriptor travels to the worker,
        // where it is unserialized again: the rebuilt test case works on
        // objects of its own, not on the ones the parent process described.
        $this->assertEquals($object, $rebuilt[0]);
        $this->assertNotSame($object, $rebuilt[0]);
    }

    public function testRebuildsTheRepetitionTheTestCaseIsOneOf(): void
    {
        $described = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $described->setRepetition(2, 5);

        $test = $this->rebuild($described);

        $this->assertSame(2, $test->repetition());
        $this->assertSame(5, $test->totalRepetitions());
    }

    public function testRebuildsTheAttemptTheTestCaseIsOneOf(): void
    {
        $described = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $described->setAttempt(3, 4);

        $test = $this->rebuild($described);

        $this->assertSame(3, $test->attempt());
        $this->assertSame(4, $test->maxAttempts());
    }

    public function testCannotDescribeATestCaseWhoseDataCannotBeSerialized(): void
    {
        $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $test->setData(0, [$this->valueThatCannotBeSerialized()]);

        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage(
            'The tests of class ' . WorkerFirstTest::class . ' cannot be run in parallel because their data cannot be serialized',
        );

        TestCaseDescriptor::fromTestCase($test, WorkerFirstTest::class);
    }

    public function testCannotDescribeATestCaseWhoseDependencyInputCannotBeSerialized(): void
    {
        $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $test->setDependencyInput(['WorkerFirstTest::testThatIsDependedUpon' => $this->valueThatCannotBeSerialized()]);

        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage(
            'The tests of class ' . WorkerFirstTest::class . ' cannot be run in parallel because their data cannot be serialized',
        );

        TestCaseDescriptor::fromTestCase($test, WorkerFirstTest::class);
    }

    /**
     * Describe the test case as the parent process does and rebuild it as the
     * worker process does.
     */
    private function rebuild(WorkerFirstTest $test): TestCase
    {
        return TestCaseDescriptor::fromTestCase($test, WorkerFirstTest::class)->test(WorkerFirstTest::class);
    }

    /**
     * A closure cannot be serialized, and a test case that carries one can
     * therefore not be described for transport to a worker.
     */
    private function valueThatCannotBeSerialized(): Closure
    {
        return static function (): void
        {
        };
    }
}
