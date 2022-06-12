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

use const E_USER_DEPRECATED;
use const E_USER_ERROR;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use function getcwd;
use function trigger_error;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\TestFixture\ChangeCurrentWorkingDirectoryTest;
use PHPUnit\TestFixture\ExceptionInAssertPostConditionsTest;
use PHPUnit\TestFixture\ExceptionInAssertPreConditionsTest;
use PHPUnit\TestFixture\ExceptionInSetUpTest;
use PHPUnit\TestFixture\ExceptionInTearDownTest;
use PHPUnit\TestFixture\ExceptionInTest;
use PHPUnit\TestFixture\ExceptionInTestDetectedInTeardown;
use PHPUnit\TestFixture\Mockable;
use PHPUnit\TestFixture\TestAutoreferenced;
use PHPUnit\TestFixture\TestWithDifferentNames;
use PHPUnit\TestFixture\TestWithDifferentOutput;
use PHPUnit\TestFixture\TestWithDifferentStatuses;
use PHPUnit\TestFixture\WasRun;

#[ExcludeGlobalVariableFromBackup('i')]
#[ExcludeGlobalVariableFromBackup('singleton')]
class TestCaseTest extends TestCase
{
    protected static int $testStatic = 456;

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['a']  = 'a';
        $_ENV['b']     = 'b';
        $_POST['c']    = 'c';
        $_GET['d']     = 'd';
        $_COOKIE['e']  = 'e';
        $_SERVER['f']  = 'f';
        $_FILES['g']   = 'g';
        $_REQUEST['h'] = 'h';
        $GLOBALS['i']  = 'i';
    }

    public static function tearDownAfterClass(): void
    {
        unset(
            $GLOBALS['a'],
            $_ENV['b'],
            $_POST['c'],
            $_GET['d'],
            $_COOKIE['e'],
            $_SERVER['f'],
            $_FILES['g'],
            $_REQUEST['h'],
            $GLOBALS['i']
        );
    }

    public function testCaseToString(): void
    {
        $this->assertEquals(
            sprintf(
                '%s::testCaseToString',
                self::class
            ),
            $this->toString()
        );
    }

    public function testCaseDefaultExecutionOrderDependencies(): void
    {
        $this->assertInstanceOf(Reorderable::class, $this);

        $this->assertEquals(
            [new ExecutionOrderDependency(static::class, 'testCaseDefaultExecutionOrderDependencies')],
            $this->provides()
        );

        $this->assertEquals(
            [],
            $this->requires()
        );
    }

    public function testExceptionInSetUp(): void
    {
        $test = new ExceptionInSetUpTest('testSomething');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->setUp);
        $this->assertFalse($test->assertPreConditions);
        $this->assertFalse($test->testSomething);
        $this->assertFalse($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
    }

    public function testExceptionInAssertPreConditions(): void
    {
        $test = new ExceptionInAssertPreConditionsTest('testSomething');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->setUp);
        $this->assertTrue($test->assertPreConditions);
        $this->assertFalse($test->testSomething);
        $this->assertFalse($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
    }

    public function testExceptionInTest(): void
    {
        $test = new ExceptionInTest('testSomething');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->setUp);
        $this->assertTrue($test->assertPreConditions);
        $this->assertTrue($test->testSomething);
        $this->assertFalse($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
    }

    public function testExceptionInAssertPostConditions(): void
    {
        $test = new ExceptionInAssertPostConditionsTest('testSomething');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->setUp);
        $this->assertTrue($test->assertPreConditions);
        $this->assertTrue($test->testSomething);
        $this->assertTrue($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
    }

    public function testExceptionInTearDown(): void
    {
        $test = new ExceptionInTearDownTest('testSomething');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->setUp);
        $this->assertTrue($test->assertPreConditions);
        $this->assertTrue($test->testSomething);
        $this->assertTrue($test->assertPostConditions);
        $this->assertTrue($test->tearDown);
        $this->assertTrue($test->status()->isError());
        $this->assertSame('throw Exception in tearDown()', $test->status()->message());
    }

    public function testExceptionInTestIsDetectedInTeardown(): void
    {
        $test = new ExceptionInTestDetectedInTeardown('testSomething');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->exceptionDetected);
    }

    public function testWasRun(): void
    {
        $test = new WasRun('testOne');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->wasRun());
    }

    public function testCurrentWorkingDirectoryIsRestored(): void
    {
        $expectedCwd = getcwd();

        $test = new ChangeCurrentWorkingDirectoryTest('testSomethingThatChangesTheCwd');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertSame($expectedCwd, getcwd());
    }

    public function testCreateMockFromClassName(): void
    {
        $mock = $this->createMock(Mockable::class);

        $this->assertInstanceOf(Mockable::class, $mock);
        $this->assertInstanceOf(MockObject::class, $mock);
    }

    public function testCreateMockMocksAllMethods(): void
    {
        $mock = $this->createMock(Mockable::class);

        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }

    public function testCreateStubFromClassName(): void
    {
        $mock = $this->createStub(Mockable::class);

        $this->assertInstanceOf(Mockable::class, $mock);
        $this->assertInstanceOf(Stub::class, $mock);
    }

    public function testCreateStubStubsAllMethods(): void
    {
        $mock = $this->createStub(Mockable::class);

        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }

    public function testCreatePartialMockDoesNotMockAllMethods(): void
    {
        /** @var Mockable $mock */
        $mock = $this->createPartialMock(Mockable::class, ['mockableMethod']);

        $this->assertNull($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testCreatePartialMockCanMockNoMethods(): void
    {
        /** @var Mockable $mock */
        $mock = $this->createPartialMock(Mockable::class, []);

        $this->assertTrue($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testCreatePartialMockWithFakeMethods(): void
    {
        $test = new TestWithDifferentStatuses('testWithCreatePartialMockWarning');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->status()->isError());
        $this->assertTrue($test->hasFailed());
    }

    public function testCreatePartialMockWithRealMethods(): void
    {
        $test = new TestWithDifferentStatuses('testWithCreatePartialMockPassesNoWarning');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->status()->isSuccess());
        $this->assertFalse($test->hasFailed());
    }

    public function testCreateMockSkipsConstructor(): void
    {
        $mock = $this->createMock(Mockable::class);

        $this->assertNull($mock->constructorArgs);
    }

    public function testCreateMockDisablesOriginalClone(): void
    {
        $mock = $this->createMock(Mockable::class);

        $cloned = clone $mock;
        $this->assertNull($cloned->cloned);
    }

    public function testCreateStubSkipsConstructor(): void
    {
        $mock = $this->createStub(Mockable::class);

        $this->assertNull($mock->constructorArgs);
    }

    public function testCreateStubDisablesOriginalClone(): void
    {
        $mock = $this->createStub(Mockable::class);

        $cloned = clone $mock;
        $this->assertNull($cloned->cloned);
    }

    public function testConfiguredMockCanBeCreated(): void
    {
        /** @var Mockable $mock */
        $mock = $this->createConfiguredMock(
            Mockable::class,
            [
                'mockableMethod' => false,
            ]
        );

        $this->assertFalse($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }

    public function testProvidingOfAutoreferencedArray(): void
    {
        $test = new TestAutoreferenced('testJsonEncodeException');

        $test->setData(0, $this->getAutoreferencedArray());

        Facade::suspend();
        $test->runBare();
        Facade::resume();

        $this->assertIsArray($test->myTestData);
        $this->assertArrayHasKey('data', $test->myTestData);
        $this->assertEquals($test->myTestData['data'][0], $test->myTestData['data']);
    }

    public function testProvidingArrayThatMixesObjectsAndScalars(): void
    {
        $data = [
            [123],
            ['foo'],
            [$this->createMock(Mockable::class)],
            [$this->createStub(Mockable::class)],
        ];

        $test = new TestAutoreferenced('testJsonEncodeException');

        $test->setData(0, [$data]);

        Facade::suspend();
        $test->runBare();
        Facade::resume();

        $this->assertIsArray($test->myTestData);
        $this->assertSame($data, $test->myTestData);
    }

    public function testGetNameReturnsMethodName(): void
    {
        $methodName = 'testWithName';

        $testCase = new TestWithDifferentNames($methodName);

        $this->assertSame($methodName, $testCase->getName());
    }

    public function testHasFailedReturnsFalseWhenTestHasNotRunYet(): void
    {
        $test = new TestWithDifferentStatuses('testThatPasses');

        $this->assertTrue($test->status()->isUnknown());
        $this->assertFalse($test->hasFailed());
    }

    public function testHasFailedReturnsTrueWhenTestHasFailed(): void
    {
        $test = new TestWithDifferentStatuses('testThatFails');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->status()->isFailure());
        $this->assertTrue($test->hasFailed());
    }

    public function testHasFailedReturnsTrueWhenTestHasErrored(): void
    {
        $test = new TestWithDifferentStatuses('testThatErrors');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->status()->isError());
        $this->assertTrue($test->hasFailed());
    }

    public function testHasFailedReturnsFalseWhenTestHasPassed(): void
    {
        $test = new TestWithDifferentStatuses('testThatPasses');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->status()->isSuccess());
        $this->assertFalse($test->hasFailed());
    }

    public function testHasFailedReturnsFalseWhenTestHasBeenMarkedAsIncomplete(): void
    {
        $test = new TestWithDifferentStatuses('testThatIsMarkedAsIncomplete');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->status()->isIncomplete());
        $this->assertFalse($test->hasFailed());
    }

    public function testHasFailedReturnsFalseWhenTestHasBeenMarkedAsRisky(): void
    {
        $test = new TestWithDifferentStatuses('testThatIsMarkedAsRisky');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->status()->isRisky());
        $this->assertFalse($test->hasFailed());
    }

    public function testHasFailedReturnsFalseWhenTestHasBeenMarkedAsSkipped(): void
    {
        $test = new TestWithDifferentStatuses('testThatIsMarkedAsSkipped');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->status()->isSkipped());
        $this->assertFalse($test->hasFailed());
    }

    public function testHasFailedReturnsFalseWhenTestHasEmittedWarning(): void
    {
        $test = new TestWithDifferentStatuses('testThatAddsAWarning');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->status()->isWarning());
        $this->assertFalse($test->hasFailed());
    }

    public function testHasOutputReturnsFalseWhenTestDoesNotGenerateOutput(): void
    {
        $test = new TestWithDifferentOutput('testThatDoesNotGenerateOutput');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertFalse($test->hasOutput());
    }

    public function testHasOutputReturnsFalseWhenTestExpectsOutputRegex(): void
    {
        $test = new TestWithDifferentOutput('testThatExpectsOutputRegex');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertFalse($test->hasOutput());
    }

    public function testHasOutputReturnsFalseWhenTestExpectsOutputString(): void
    {
        $test = new TestWithDifferentOutput('testThatExpectsOutputString');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertFalse($test->hasOutput());
    }

    public function testHasOutputReturnsTrueWhenTestGeneratesOutput(): void
    {
        $test = new TestWithDifferentOutput('testThatGeneratesOutput');

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertTrue($test->hasOutput());
    }

    public function testDeprecationCanBeExpected(): void
    {
        $this->expectDeprecation();
        $this->expectDeprecationMessage('foo');
        $this->expectDeprecationMessageMatches('/foo/');

        trigger_error('foo', E_USER_DEPRECATED);
    }

    public function testNoticeCanBeExpected(): void
    {
        $this->expectNotice();
        $this->expectNoticeMessage('foo');
        $this->expectNoticeMessageMatches('/foo/');

        trigger_error('foo', E_USER_NOTICE);
    }

    public function testWarningCanBeExpected(): void
    {
        $this->expectWarning();
        $this->expectWarningMessage('foo');
        $this->expectWarningMessageMatches('/foo/');

        trigger_error('foo', E_USER_WARNING);
    }

    public function testErrorCanBeExpected(): void
    {
        $this->expectError();
        $this->expectErrorMessage('foo');
        $this->expectErrorMessageMatches('/foo/');

        trigger_error('foo', E_USER_ERROR);
    }

    /**
     * @return array<string, array>
     */
    private function getAutoreferencedArray(): array
    {
        $recursionData   = [];
        $recursionData[] = &$recursionData;

        return [
            'RECURSION' => [
                'data' => $recursionData,
            ],
        ];
    }
}
