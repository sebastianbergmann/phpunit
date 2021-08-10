<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\MockObject\CannotUseAddMethodsException;
use PHPUnit\Framework\MockObject\CannotUseOnlyMethodsException;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Mockable;

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
                     ->onlyMethods(['mockableMethod'])
                     ->getMock();

        $this->assertNull($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
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
        $this->expectException(CannotUseOnlyMethodsException::class);
        $this->expectExceptionMessage('Trying to configure method "mockableMethodWithCrazyName" with onlyMethods(), but it does not exist in class "PHPUnit\TestFixture\Mockable". Use addMethods() for methods that do not exist in the class');

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
        $this->expectException(CannotUseAddMethodsException::class);
        $this->expectExceptionMessage('Trying to configure method "mockableMethod" with addMethods(), but it exists in class "PHPUnit\TestFixture\Mockable". Use onlyMethods() for methods that exist in the class');

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
                     ->onlyMethods(['mockableMethod'])
                     ->setConstructorArgs([])
                     ->setMockClassName('DummyClassName')
                     ->disableOriginalConstructor()
                     ->disableOriginalClone()
                     ->disableAutoload();

        $this->assertInstanceOf(MockBuilder::class, $spec);
    }
}
