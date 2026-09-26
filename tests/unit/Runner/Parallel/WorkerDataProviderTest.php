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

use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorker\WorkerDataProvidedTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;

#[CoversClass(WorkerDataProvider::class)]
#[Small]
final class WorkerDataProviderTest extends TestCase
{
    public function testProvidesTheDataSetOfTheGivenNameByInvokingTheDataProvider(): void
    {
        $dataProvider = $this->dataProvider();

        $this->assertSame([1], $dataProvider->dataSet(WorkerDataProvidedTest::class, 'testWithNamedDataSets', 'first data set'));
        $this->assertSame([2], $dataProvider->dataSet(WorkerDataProvidedTest::class, 'testWithNamedDataSets', 'second data set'));
    }

    public function testProvidesADataSetThatTheDataProviderKeyedByNumber(): void
    {
        $this->assertSame([2], $this->dataProvider()->dataSet(WorkerDataProvidedTest::class, 'testWithNumberedDataSets', 1));
    }

    public function testProvidesObjectsThatTheDataProviderOfThisProcessCreated(): void
    {
        $dataSet = $this->dataProvider()->dataSet(WorkerDataProvidedTest::class, 'testWithAnObject', 0);

        $this->assertSame('provided by the data provider', $dataSet[0]->value);
    }

    public function testInvokesTheDataProviderOfATestMethodOnlyOnce(): void
    {
        $dataProvider = $this->dataProvider();

        $invocations = WorkerDataProvidedTest::$namedProviderInvocations;

        $dataProvider->dataSet(WorkerDataProvidedTest::class, 'testWithNamedDataSets', 'first data set');
        $dataProvider->dataSet(WorkerDataProvidedTest::class, 'testWithNamedDataSets', 'second data set');

        $this->assertSame($invocations + 1, WorkerDataProvidedTest::$namedProviderInvocations);
    }

    public function testCannotProvideADataSetThatTheDataProviderDoesNotProvide(): void
    {
        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage(
            'The data provider for ' . WorkerDataProvidedTest::class . '::testWithNamedDataSets did not provide data set "third data set" when the worker process invoked it',
        );

        $this->dataProvider()->dataSet(WorkerDataProvidedTest::class, 'testWithNamedDataSets', 'third data set');
    }

    public function testNamesANumberedDataSetThatTheDataProviderDoesNotProvideByItsNumber(): void
    {
        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage('did not provide data set #2');

        $this->dataProvider()->dataSet(WorkerDataProvidedTest::class, 'testWithNumberedDataSets', 2);
    }

    public function testCannotProvideDataWhenTheDataProviderFails(): void
    {
        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage(
            'The data provider for ' . WorkerDataProvidedTest::class . '::testWithAFailingDataProvider failed when the worker process invoked it',
        );

        $this->dataProvider()->dataSet(WorkerDataProvidedTest::class, 'testWithAFailingDataProvider', 0);
    }

    public function testCannotProvideDataForATestMethodThatDoesNotDeclareADataProvider(): void
    {
        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage(
            WorkerFirstTest::class . '::testStartsTheProcessLocalCounter does not declare a data provider',
        );

        $this->dataProvider()->dataSet(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter', 0);
    }

    private function dataProvider(): WorkerDataProvider
    {
        return new WorkerDataProvider($this->createStub(Emitter::class));
    }
}
