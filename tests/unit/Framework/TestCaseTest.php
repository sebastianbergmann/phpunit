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

use function sprintf;
use PHPUnit\Event;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup;
use PHPUnit\Framework\TestCase\GlobalStateCapture;
use PHPUnit\TestFixture\TestWithDifferentNames;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

#[CoversClass(TestCase::class)]
#[ExcludeGlobalVariableFromBackup('i')]
#[ExcludeGlobalVariableFromBackup('singleton')]
class TestCaseTest extends TestCase
{
    protected static int $testStatic = 456;

    /**
     * @return iterable<array{string, int|string, array<mixed>}>
     */
    public static function provideDataSetAsStringWithDataProvider(): iterable
    {
        yield ['', 'dataSet', []];

        yield ["#0 with data ('foo', 'bar')", 0, ['foo', 'bar']];

        yield ["@dataSet with data ('foo', 'bar')", 'dataSet', ['foo', 'bar']];
    }

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
            $GLOBALS['i'],
        );
    }

    public function testCaseToString(): void
    {
        $this->assertEquals(
            sprintf(
                '%s::testCaseToString',
                self::class,
            ),
            $this->toString(),
        );
    }

    public function testCaseDefaultExecutionOrderDependencies(): void
    {
        $this->assertInstanceOf(Reorderable::class, $this);

        $this->assertEquals(
            [new ExecutionOrderDependency(static::class, 'testCaseDefaultExecutionOrderDependencies')],
            $this->provides(),
        );

        $this->assertEquals(
            [],
            $this->requires(),
        );
    }

    public function testGetNameReturnsMethodName(): void
    {
        $methodName = 'testWithName';

        $testCase = new TestWithDifferentNames($methodName);

        $this->assertSame($methodName, $testCase->nameWithDataSet());
    }

    #[DataProvider('provideDataSetAsStringWithDataProvider')]
    public function testDataSetAsStringWithData(string $expectedData, int|string $dataName, array $data): void
    {
        $testCase = new TestWithDifferentNames('testWithName');
        $testCase->setData($dataName, $data);

        $this->assertSame($expectedData, $testCase->dataSetAsStringWithData());
    }

    public function testSortIdWithDataProvider(): void
    {
        $testCase = new TestWithDifferentNames('testWithName');
        $testCase->setData(0, ['foo']);

        $this->assertSame(
            TestWithDifferentNames::class . '::testWithName with data set #0',
            $testCase->sortId(),
        );
    }

    public function testSortIdWithNamedDataSet(): void
    {
        $testCase = new TestWithDifferentNames('testWithName');
        $testCase->setData('myDataSet', ['foo']);

        $this->assertSame(
            TestWithDifferentNames::class . '::testWithName with data set "myDataSet"',
            $testCase->sortId(),
        );
    }

    public function testShouldRunInSeparateProcessReturnsFalseWhenTestIsAlreadyInIsolation(): void
    {
        $testCase = new TestWithDifferentNames('testWithName');
        $testCase->setInIsolation(true);

        $method = new ReflectionMethod(TestCase::class, 'shouldRunInSeparateProcess');

        $this->assertFalse($method->invoke($testCase));
    }

    public function testShouldInvocationMockerBeResetReturnsFalseWhenMockIsAmongDependencyInput(): void
    {
        $testCase = new TestWithDifferentNames('testWithName');
        $mock     = $this->createMock(stdClass::class);

        $testCase->setDependencyInput(['previousTest' => $mock]);

        $method = new ReflectionMethod(TestCase::class, 'shouldInvocationMockerBeReset');

        $this->assertFalse($method->invoke($testCase, $mock));
    }

    public function testShouldInvocationMockerBeResetReturnsFalseWhenMockIsAmongTestResult(): void
    {
        $testCase = new TestWithDifferentNames('testWithName');
        $mock     = $this->createMock(stdClass::class);

        $testCase->setResult([$mock]);

        $method = new ReflectionMethod(TestCase::class, 'shouldInvocationMockerBeReset');

        $this->assertFalse($method->invoke($testCase, $mock));
    }

    public function testCreateGlobalStateSnapshotAppliesBackupStaticPropertiesExcludeList(): void
    {
        $testCase = new TestWithDifferentNames('testWithName');

        $testCase->setBackupStaticPropertiesExcludeList([
            self::class => ['testStatic'],
        ]);

        $capture = new ReflectionProperty(TestCase::class, 'globalStateCapture')->getValue($testCase);

        $this->assertInstanceOf(GlobalStateCapture::class, $capture);
        $this->assertNotNull($capture->createSnapshot($testCase, Event\Facade::emitter(), true));
    }
}
