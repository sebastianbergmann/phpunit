<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function sprintf;
use ACustomClassname;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Mockable;

#[CoversClass(MockBuilder::class)]
#[Small]
final class MockBuilderTest extends TestCase
{
    public function testMockBuilderRequiresClassName(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)->getMock();

        $this->assertInstanceOf(Mockable::class, $mock);
    }

    public function testByDefaultMocksAllMethods(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)->getMock();

        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }

    public function testMethodsToMockCanBeSpecified(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->onlyMethods(['mockableMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testOnlyMethodsWithNonExistentMethodNames(): void
    {
        $this->expectException(CannotUseOnlyMethodsException::class);
        $this->expectExceptionMessage(sprintf(
            'Trying to configure method "mockableMethodWithCrazyName" with onlyMethods(), but it does not exist in class "%s". Use addMethods() for methods that do not exist in the class',
            Mockable::class
        ));

        $this->getMockBuilder(Mockable::class)
             ->onlyMethods(['mockableMethodWithCrazyName'])
             ->getMock();
    }

    public function testOnlyMethodsWithExistingMethodNames(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->onlyMethods(['mockableMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testOnlyMethodsWithEmptyArray(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->onlyMethods([])
                     ->getMock();

        $this->assertTrue($mock->mockableMethod());
    }

    public function testAddMethodsWithExistentMethodNames(): void
    {
        $this->expectException(CannotUseAddMethodsException::class);
        $this->expectExceptionMessage(sprintf(
            'Trying to configure method "mockableMethod" with addMethods(), but it exists in class "%s". Use onlyMethods() for methods that exist in the class',
            Mockable::class
        ));

        $this->getMockBuilder(Mockable::class)
             ->addMethods(['mockableMethod'])
             ->getMock();
    }

    public function testAddMethodsWithNonExistingMethodNames(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->addMethods(['mockableMethodWithFakeMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethodWithFakeMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testAddMethodsWithEmptyArray(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->addMethods([])
                     ->getMock();

        $this->assertTrue($mock->mockableMethod());
    }

    public function testAbleToUseAddMethodsAfterOnlyMethods(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->onlyMethods(['mockableMethod'])
                     ->addMethods(['mockableMethodWithFakeMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->mockableMethodWithFakeMethod());
    }

    public function testAbleToUseOnlyMethodsAfterAddMethods(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->addMethods(['mockableMethodWithFakeMethod'])
                     ->onlyMethods(['mockableMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethodWithFakeMethod());
        $this->assertNull($mock->mockableMethod());
    }

    public function testByDefaultDoesNotPassArgumentsToTheConstructor(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)->getMock();

        $this->assertEquals([null, null], $mock->constructorArgs);
    }

    public function testMockClassNameCanBeSpecified(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->setMockClassName(ACustomClassName::class)
                     ->getMock();

        $this->assertInstanceOf('ACustomClassName', $mock);
    }

    public function testConstructorArgumentsCanBeSpecified(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->setConstructorArgs([23, 42])
                     ->getMock();

        $this->assertEquals([23, 42], $mock->constructorArgs);
    }

    public function testOriginalConstructorCanBeDisabled(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $this->assertNull($mock->constructorArgs);
    }

    public function testByDefaultOriginalCloneIsPreserved(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->getMock();

        $cloned = clone $mock;

        $this->assertTrue($cloned->cloned);
    }

    public function testOriginalCloneCanBeDisabled(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->disableOriginalClone()
                     ->getMock();

        $mock->cloned = false;
        $cloned       = clone $mock;

        $this->assertFalse($cloned->cloned);
    }

    public function testProvidesAFluentInterface(): void
    {
        $spec = $this->getMockBuilder(Mockable::class)
                     ->onlyMethods(['mockableMethod'])
                     ->setConstructorArgs([])
                     ->setMockClassName('DummyClassName')
                     ->disableOriginalConstructor()
                     ->disableOriginalClone()
                     ->disableAutoload();

        $this->assertInstanceOf(MockBuilder::class, $spec);
    }
}
