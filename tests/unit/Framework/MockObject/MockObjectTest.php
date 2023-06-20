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

use function call_user_func_array;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
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

    public function testExpectationThatMethodIsNeverCalledSucceedsWhenMethodIsNotCalled(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->never())->method('doSomething');
    }

    public function testExpectationThatMethodIsNeverCalledFailsWhenMethodIsCalled(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->never())->method('doSomething');

        $this->assertThatMockObjectExpectationFails(
            AnInterface::class . '::doSomething() was not expected to be called.',
            $mock,
            'doSomething',
        );
    }

    #[DoesNotPerformAssertions]
    public function testExpectationThatMethodIsCalledZeroOrMoreTimesSucceedsWhenMethodIsNotCalled(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->any())->method('doSomething');
    }

    #[DoesNotPerformAssertions]
    public function testExpectationThatMethodIsCalledZeroOrMoreTimesSucceedsWhenMethodIsCalledOnce(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->any())->method('doSomething');

        $mock->doSomething();
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

        $this->assertThatMockObjectExpectationFails(
            AnInterface::class . '::doSomething() was not expected to be called more than once.',
            $mock,
            'doSomething',
        );
    }

    public function testExpectationThatMethodIsCalledAtLeastOnceSucceedsWhenMethodIsCalledOnce(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->atLeastOnce())->method('doSomething');

        $mock->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtLeastOnceSucceedsWhenMethodIsCalledTwice(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->atLeastOnce())->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtLeastTwiceSucceedsWhenMethodIsCalledTwice(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->atLeast(2))->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtLeastTwiceSucceedsWhenMethodIsCalledThreeTimes(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->atLeast(2))->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
        $mock->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtLeastOnceFailsWhenMethodIsNotCalled(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->atLeastOnce())->method('doSomething');

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked at least once.
Expected invocation at least once but it never occurred.

EOT,
            $mock,
        );
    }

    public function testExpectationThatMethodIsCalledAtLeastTwiceFailsWhenMethodIsCalledOnce(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->atLeast(2))->method('doSomething');

        $mock->doSomething();

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked at least 2 times.
Expected invocation at least 2 times but it occurred 1 time.

EOT,
            $mock,
        );
    }

    public function testExpectationThatMethodIsCalledTwiceSucceedsWhenMethodIsCalledTwice(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->exactly(2))->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();
    }

    public function testExpectationThatMethodIsCalledTwiceFailsWhenMethodIsNeverCalled(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->exactly(2))->method('doSomething');

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 2 times.
Method was expected to be called 2 times, actually called 0 times.

EOT,
            $mock,
        );
    }

    public function testExpectationThatMethodIsCalledTwiceFailsWhenMethodIsCalledOnce(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->exactly(2))->method('doSomething');

        $mock->doSomething();

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 2 times.
Method was expected to be called 2 times, actually called 1 time.

EOT,
            $mock,
        );
    }

    public function testExpectationThatMethodIsCalledTwiceFailsWhenMethodIsCalledThreeTimes(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->exactly(2))->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();

        $this->assertThatMockObjectExpectationFails(
            AnInterface::class . '::doSomething() was not expected to be called more than 2 times.',
            $mock,
            'doSomething',
        );
    }

    public function testExpectationThatMethodIsCalledWithParameterSucceedsWhenMethodIsCalledWithExpectedParameter(): void
    {
        $mock = $this->createMock(InterfaceWithReturnTypeDeclaration::class);

        $mock->expects($this->once())->method('doSomethingElse')->with(1);

        $mock->doSomethingElse(1);
    }

    public function testExpectationThatMethodIsCalledWithParameterFailsWhenMethodIsCalledButWithUnexpectedParameter(): void
    {
        $mock = $this->createMock(InterfaceWithReturnTypeDeclaration::class);

        $mock->expects($this->once())->method('doSomethingElse')->with(1);

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomethingElse" when invoked 1 time
Parameter 0 for invocation PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration::doSomethingElse(0): int does not match expected value.
Failed asserting that 0 matches expected 1.
EOT,
            $mock,
            'doSomethingElse',
            [0],
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

    private function assertThatMockObjectExpectationFails(string $expectationFailureMessage, MockObject $mock, string $methodName = '__phpunit_verify', array $arguments = []): void
    {
        try {
            call_user_func_array([$mock, $methodName], $arguments);
        } catch (ExpectationFailedException $e) {
            $this->assertSame($expectationFailureMessage, $e->getMessage());

            return;
        } finally {
            (new ReflectionProperty(TestCase::class, 'mockObjects'))->setValue($this, []);
        }

        $this->fail();
    }
}
