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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\TestFixture\Mockable;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterface;
use PHPUnit\TestFixture\TestWithDifferentNames;

#[CoversClass(TestCase::class)]
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

    public function testGetNameReturnsMethodName(): void
    {
        $methodName = 'testWithName';

        $testCase = new TestWithDifferentNames($methodName);

        $this->assertSame($methodName, $testCase->nameWithDataSet());
    }

    public function testStubCanBeCreatedForIntersectionOfInterfaces(): void
    {
        $intersection = $this->createStubForIntersectionOfInterfaces([AnInterface::class, AnotherInterface::class]);

        $this->assertInstanceOf(AnInterface::class, $intersection);
        $this->assertInstanceOf(AnotherInterface::class, $intersection);
        $this->assertInstanceOf(Stub::class, $intersection);
    }

    public function testMockObjectCanBeCreatedForIntersectionOfInterfaces(): void
    {
        $intersection = $this->createMockForIntersectionOfInterfaces([AnInterface::class, AnotherInterface::class]);

        $this->assertInstanceOf(AnInterface::class, $intersection);
        $this->assertInstanceOf(AnotherInterface::class, $intersection);
        $this->assertInstanceOf(MockObject::class, $intersection);
    }
}
