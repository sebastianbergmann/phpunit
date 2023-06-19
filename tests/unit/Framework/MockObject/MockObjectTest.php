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
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;
use ReflectionProperty;

#[Group('test-doubles')]
#[Medium]
final class MockObjectTest extends TestDoubleTestCase
{
    #[TestDox('createConfiguredMock() can be used to create a mock object and configure the return value for multiple methods')]
    public function test_createConfiguredMock_works(): void
    {
        $mock = $this->createConfiguredMock(
            InterfaceWithReturnTypeDeclaration::class,
            [
                'doSomething'     => true,
                'doSomethingElse' => 1,
            ],
        );

        $this->assertTrue($mock->doSomething());
        $this->assertSame(1, $mock->doSomethingElse(0));
    }

    public function testExpectationThatMethodIsCalledOnceSucceedsWhenMethodIsCalledOnce(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->once())->method('doSomething');

        $mock->doSomething();
    }

    public function testExpectationThatMethodIsCalledOnceFailsWhenMethodIsNeverCalled(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->once())->method('doSomething');

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 1 time.
Method was expected to be called 1 time, actually called 0 times.

EOT,
            $mock,
        );
    }

    public function testExpectationThatMethodIsCalledOnceFailsWhenMethodIsCalledMoreThanOnce(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->once())->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 1 time.
Method was expected to be called 1 time, actually called 2 times.

EOT,
            $mock,
        );
    }

    /**
     * @psalm-param class-string $type
     */
    protected function createTestDouble(string $type): object
    {
        return $this->createMock($type);
    }

    /**
     * @psalm-param list<class-string> $interfaces
     */
    protected function createTestDoubleForIntersection(array $interfaces): object
    {
        return $this->createMockForIntersectionOfInterfaces($interfaces);
    }

    private function assertThatMockObjectExpectationFails(string $expectationFailureMessage, MockObject $mock): void
    {
        try {
            $mock->__phpunit_verify();
        } catch (ExpectationFailedException $e) {
            $this->assertSame($expectationFailureMessage, $e->getMessage());

            return;
        } finally {
            (new ReflectionProperty(TestCase::class, 'mockObjects'))->setValue($this, []);
        }

        $this->fail();
    }
}
