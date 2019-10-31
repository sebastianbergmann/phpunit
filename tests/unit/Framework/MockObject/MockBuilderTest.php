<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
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
                     ->setMethods(['mockableMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testMethodExceptionsToMockCanBeSpecified(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->setMethodsExcept(['mockableMethod'])
                     ->getMock();

        $this->assertTrue($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
    }

    public function testSetMethodsAllowsNonExistentMethodNames(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->setMethods(['mockableMethodWithCrazyName'])
                     ->getMock();

        $this->assertNull($mock->mockableMethodWithCrazyName());
    }

    public function testOnlyMethodsWithNonExistentMethodNames(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Trying to set mock method "mockableMethodWithCrazyName" with onlyMethods, but it does not exist in class "Mockable". Use addMethods() for methods that don\'t exist in the class.');

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

    public function testAddMethodsWithNonExistentMethodNames(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Trying to set mock method "mockableMethod" with addMethods(), but it exists in class "Mockable". Use onlyMethods() for methods that exist in the class.');

        $this->getMockBuilder(Mockable::class)
             ->addMethods(['mockableMethod'])
             ->getMock();
    }

    public function testAddMethodsWithExistingMethodNames(): void
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

    public function testEmptyMethodExceptionsToMockCanBeSpecified(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->setMethodsExcept()
                     ->getMock();

        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->anotherMockableMethod());
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

    public function testAbleToUseSetMethodsAfterOnlyMethods(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->onlyMethods(['mockableMethod'])
                     ->setMethods(['mockableMethodWithCrazyName'])
                     ->getMock();

        $this->assertNull($mock->mockableMethodWithCrazyName());
    }

    public function testAbleToUseSetMethodsAfterAddMethods(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->addMethods(['notAMethod'])
                     ->setMethods(['mockableMethodWithCrazyName'])
                     ->getMock();

        $this->assertNull($mock->mockableMethodWithCrazyName());
    }

    public function testAbleToUseAddMethodsAfterSetMethods(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->setMethods(['mockableMethod'])
                     ->addMethods(['mockableMethodWithFakeMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->mockableMethodWithFakeMethod());
    }

    public function testAbleToUseOnlyMethodsAfterSetMethods(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->setMethods(['mockableMethodWithFakeMethod'])
                     ->onlyMethods(['mockableMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethod());
        $this->assertNull($mock->mockableMethodWithFakeMethod());
    }

    public function testAbleToUseAddMethodsAfterSetMethodsWithNull(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->setMethods()
                     ->addMethods(['mockableMethodWithFakeMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethodWithFakeMethod());
    }

    public function testByDefaultDoesNotPassArgumentsToTheConstructor(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)->getMock();

        $this->assertEquals([null, null], $mock->constructorArgs);
    }

    public function testMockClassNameCanBeSpecified(): void
    {
        $mock = $this->getMockBuilder(Mockable::class)
                     ->setMockClassName('ACustomClassName')
                     ->getMock();

        $this->assertInstanceOf(ACustomClassName::class, $mock);
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
                     ->setMethods(['mockableMethod'])
                     ->setConstructorArgs([])
                     ->setMockClassName('DummyClassName')
                     ->disableOriginalConstructor()
                     ->disableOriginalClone()
                     ->disableAutoload();

        $this->assertInstanceOf(MockBuilder::class, $spec);
    }
}
