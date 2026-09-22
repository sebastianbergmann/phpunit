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
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorker\WorkerDataProvidedTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;

#[CoversClass(TestCaseDescriptor::class)]
#[UsesClass(WorkerDataProvider::class)]
#[Small]
final class TestCaseDescriptorTest extends TestCase
{
    public function testRebuildsTheTestMethodItDescribes(): void
    {
        $test = $this->rebuild(new WorkerFirstTest('testStartsTheProcessLocalCounter'));

        $this->assertInstanceOf(WorkerFirstTest::class, $test);
        $this->assertSame('testStartsTheProcessLocalCounter', $test->name());
        $this->assertFalse($test->usesDataProvider());
    }

    public function testRebuildsATestCaseWithTheDataSetThatTheDataProviderProvidesUnderTheDescribedName(): void
    {
        $described = new WorkerDataProvidedTest('testWithNamedDataSets');

        $described->setData('second data set', [2]);

        $test = $this->rebuild($described);

        $this->assertSame([2], $test->providedData());
        $this->assertSame('second data set', $test->dataName());
    }

    public function testRebuildsATestCaseWhoseDataSetTheDataProviderKeyedByNumber(): void
    {
        $described = new WorkerDataProvidedTest('testWithNumberedDataSets');

        $described->setData(1, [2]);

        $test = $this->rebuild($described);

        $this->assertSame([2], $test->providedData());
        $this->assertSame(1, $test->dataName());
    }

    public function testRebuildsTheDataFromTheDataProviderRatherThanFromTheDescribedTestCase(): void
    {
        // What the described test case carries as data does not travel with
        // the descriptor; the worker's invocation of the data provider is
        // what provides the data. The described data is therefore irrelevant
        // to what the rebuilt test case receives — only the name of the data
        // set counts.
        $described = new WorkerDataProvidedTest('testWithNamedDataSets');

        $described->setData('first data set', ['something else entirely']);

        $this->assertSame([1], $this->rebuild($described)->providedData());
    }

    public function testRebuildsTheNameOfADataSetThatWasProvidedAsAnEmptyArray(): void
    {
        // A data set that is an empty array leaves the test case without
        // data, so the data provider is not invoked for it; the data set's
        // name is rebuilt all the same.
        $described = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $described->setData('empty data set', []);

        $test = $this->rebuild($described);

        $this->assertSame([], $test->providedData());
        $this->assertSame('empty data set', $test->dataName());
    }

    public function testRebuildsTheInputProvidedByTheTestsTheTestCaseDependsOn(): void
    {
        $described = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $described->setDependencyInput(['WorkerFirstTest::testThatIsDependedUpon' => 'its return value']);

        $test = $this->rebuild($described);

        $this->assertSame(['WorkerFirstTest::testThatIsDependedUpon' => 'its return value'], $test->dependencyInput());
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

    public function testCannotRebuildATestCaseWhoseDataSetTheDataProviderDoesNotProvide(): void
    {
        $described = new WorkerDataProvidedTest('testWithNamedDataSets');

        $described->setData('data set the provider does not provide', [1]);

        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage('did not provide data set "data set the provider does not provide"');

        $this->rebuild($described);
    }

    public function testCannotDescribeATestCaseWhoseDependencyInputCannotBeSerialized(): void
    {
        $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $test->setDependencyInput(['WorkerFirstTest::testThatIsDependedUpon' => $this->valueThatCannotBeSerialized()]);

        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage(
            'The tests of class ' . WorkerFirstTest::class . ' cannot be run in parallel because their dependency input cannot be serialized',
        );

        TestCaseDescriptor::fromTestCase($test, WorkerFirstTest::class);
    }

    /**
     * Describe the test case as the parent process does and rebuild it as the
     * worker process does.
     */
    private function rebuild(TestCase $test): TestCase
    {
        return TestCaseDescriptor::fromTestCase($test, $test::class)->test(
            $test::class,
            new WorkerDataProvider($this->createStub(Emitter::class)),
        );
    }

    /**
     * A closure cannot be serialized, and a test case whose dependency input
     * carries one can therefore not be described for transport to a worker.
     */
    private function valueThatCannotBeSerialized(): Closure
    {
        return static function (): void
        {
        };
    }
}
