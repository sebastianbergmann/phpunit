<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use function assert;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Metadata\Attribute\DisableReturnValueGenerationForTestDoublesTest;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterface;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\TestWithDifferentNames;
use ReflectionProperty;

#[CoversClass(TestDoubleFactory::class)]
#[Small]
final class TestDoubleFactoryTest extends TestCase
{
    public function testCreatesMockObjectAndRegistersItWithTest(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testCreatedMockObject')
            ->with(AnInterface::class)
            ->seal();

        $mock = new TestDoubleFactory(TestWithDifferentNames::class, $emitter)->createMock($test, AnInterface::class);

        $this->assertInstanceOf(AnInterface::class, $mock);
        $this->assertFalse($mock->doSomething());
        $this->assertMockObjectIsRegisteredWith($test, $mock);
    }

    public function testCreatesMockObjectForIntersectionOfInterfacesAndRegistersItWithTest(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testCreatedMockObjectForIntersectionOfInterfaces')
            ->with([AnInterface::class, AnotherInterface::class])
            ->seal();

        $mock = new TestDoubleFactory(TestWithDifferentNames::class, $emitter)->createMockForIntersectionOfInterfaces(
            $test,
            [AnInterface::class, AnotherInterface::class],
        );

        $this->assertInstanceOf(AnInterface::class, $mock);
        $this->assertInstanceOf(AnotherInterface::class, $mock);
        $this->assertMockObjectIsRegisteredWith($test, $mock);
    }

    public function testCreatesConfiguredMockObject(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testCreatedMockObject')
            ->with(AnInterface::class)
            ->seal();

        $mock = new TestDoubleFactory(TestWithDifferentNames::class, $emitter)->createConfiguredMock(
            $test,
            AnInterface::class,
            ['doSomething' => true],
        );

        $this->assertTrue($mock->doSomething());
        $this->assertMockObjectIsRegisteredWith($test, $mock);
    }

    public function testCreatesPartialMockObject(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testCreatedPartialMockObject')
            ->with(ExtendableClass::class, 'doSomething')
            ->seal();

        $mock = new TestDoubleFactory(TestWithDifferentNames::class, $emitter)->createPartialMock(
            $test,
            ExtendableClass::class,
            ['doSomething'],
        );

        $this->assertInstanceOf(ExtendableClass::class, $mock);
        $this->assertFalse($mock->doSomething());
        $this->assertMockObjectIsRegisteredWith($test, $mock);
    }

    public function testCreatesStub(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testCreatedStub')
            ->with(AnInterface::class)
            ->seal();

        $stub = new TestDoubleFactory(TestWithDifferentNames::class, $emitter)->createStub(AnInterface::class);

        $this->assertInstanceOf(AnInterface::class, $stub);
        $this->assertNotInstanceOf(MockObject::class, $stub);
        $this->assertFalse($stub->doSomething());
    }

    public function testCreatesStubForIntersectionOfInterfaces(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testCreatedStubForIntersectionOfInterfaces')
            ->with([AnInterface::class, AnotherInterface::class])
            ->seal();

        $stub = new TestDoubleFactory(TestWithDifferentNames::class, $emitter)->createStubForIntersectionOfInterfaces(
            [AnInterface::class, AnotherInterface::class],
        );

        $this->assertInstanceOf(AnInterface::class, $stub);
        $this->assertInstanceOf(AnotherInterface::class, $stub);
        $this->assertNotInstanceOf(MockObject::class, $stub);
    }

    public function testCreatesConfiguredStub(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testCreatedStub')
            ->with(AnInterface::class)
            ->seal();

        $stub = new TestDoubleFactory(TestWithDifferentNames::class, $emitter)->createConfiguredStub(
            AnInterface::class,
            ['doSomething' => true],
        );

        $this->assertTrue($stub->doSomething());
    }

    public function testReturnValueGenerationCanBeDisabledForStubs(): void
    {
        $stub = new TestDoubleFactory(DisableReturnValueGenerationForTestDoublesTest::class, $this->createStub(Emitter::class))->createStub(AnInterface::class);

        $this->expectException(ReturnValueNotConfiguredException::class);

        $stub->doSomething();
    }

    public function testReturnValueGenerationCanBeDisabledForMockObjects(): void
    {
        $mock = new TestDoubleFactory(DisableReturnValueGenerationForTestDoublesTest::class, $this->createStub(Emitter::class))->createMock(
            new TestWithDifferentNames('testWithName'),
            AnInterface::class,
        );

        $this->expectException(ReturnValueNotConfiguredException::class);

        $mock->doSomething();
    }

    public function testReturnValueGenerationCanBeDisabledForPartialMockObjects(): void
    {
        $mock = new TestDoubleFactory(DisableReturnValueGenerationForTestDoublesTest::class, $this->createStub(Emitter::class))->createPartialMock(
            new TestWithDifferentNames('testWithName'),
            ExtendableClass::class,
            ['doSomething'],
        );

        $this->expectException(ReturnValueNotConfiguredException::class);

        $mock->doSomething();
    }

    private function assertMockObjectIsRegisteredWith(TestCase $test, MockObject $mock): void
    {
        $registry = new ReflectionProperty(TestCase::class, 'mockObjectRegistry')->getValue($test);

        assert($registry instanceof MockObjectRegistry);

        $mock
            ->expects($this->once())
            ->method($this->anything())
            ->willReturn(true);

        $mock->doSomething();

        $registry->verify($test, $this->createStub(Emitter::class));

        $this->assertSame(1, $test->numberOfAssertionsPerformed());
    }
}
