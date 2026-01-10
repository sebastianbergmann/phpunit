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

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;

#[Group('test-doubles')]
#[Group('test-doubles/test-stub')]
#[Medium]
#[TestDox('Test Stub')]
final class StubTest extends TestDoubleTestCase
{
    #[TestDox('Sealed stub throws exception when method() is called')]
    public function testSealedStubThrowsExceptionWhenMethodIsCalled(): void
    {
        $stub = $this->createStub(InterfaceWithReturnTypeDeclaration::class);

        $stub->method('doSomething')->willReturn(true)->seal();

        $this->expectException(TestDoubleSealedException::class);

        $stub->method('doSomethingElse');
    }

    #[TestDox('Sealed stub allows configured methods to be called')]
    public function testSealedStubAllowsConfiguredMethodsToBeCalled(): void
    {
        $stub = $this->createStub(InterfaceWithReturnTypeDeclaration::class);

        $stub->method('doSomething')->willReturn(true)->seal();

        $this->assertTrue($stub->doSomething());
    }

    #[TestDox('Sealed stub does not add never() expectation for unconfigured methods')]
    public function testSealedStubDoesNotAddNeverExpectationForUnconfiguredMethods(): void
    {
        $stub = $this->createStub(InterfaceWithReturnTypeDeclaration::class);

        $stub->method('doSomething')->willReturn(true)->seal();

        $this->assertTrue($stub->doSomething());
        $this->assertFalse($stub->doSomethingElse(0) > 0);
    }

    #[TestDox('Sealing stub twice is idempotent')]
    public function testSealingStubTwiceIsIdempotent(): void
    {
        $stub = $this->createStub(InterfaceWithReturnTypeDeclaration::class);

        $stub->method('doSomething')->willReturn(true);

        $stub->__phpunit_getInvocationHandler()->seal(false);
        $stub->__phpunit_getInvocationHandler()->seal(false);

        $this->assertTrue($stub->doSomething());
    }

    #[TestDox('Cloned sealed stub remains sealed')]
    public function testClonedSealedStubRemainsSealed(): void
    {
        $stub = $this->createStub(InterfaceWithReturnTypeDeclaration::class);

        $stub->__phpunit_getInvocationHandler()->seal(false);

        $clone = clone $stub;

        $this->expectException(TestDoubleSealedException::class);

        $clone->method('doSomethingElse');
    }

    /**
     * @param class-string $type
     */
    protected function createTestDouble(string $type): object
    {
        return $this->createStub($type);
    }
}
