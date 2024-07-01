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
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\InterfaceWithImplicitProtocol;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;
use PHPUnit\TestFixture\MockObject\MethodWIthVariadicVariables;
use ReflectionProperty;

#[Group('test-doubles')]
#[Group('test-doubles/mock-object')]
#[TestDox('Mock Object')]
#[Medium]
final class MockObjectTest extends TestDoubleTestCase
{
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
            AnInterface::class . '::doSomething(): bool was not expected to be called.',
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
            AnInterface::class . '::doSomething(): bool was not expected to be called more than once.',
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
            AnInterface::class . '::doSomething(): bool was not expected to be called more than 2 times.',
            $mock,
            'doSomething',
        );
    }

    public function testExpectationThatMethodIsCalledAtMostOnceSucceedsWhenMethodIsNeverCalled(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->atMost(1))->method('doSomething');
    }

    public function testExpectationThatMethodIsCalledAtMostOnceSucceedsWhenMethodIsCalledOnce(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->atMost(1))->method('doSomething');

        $mock->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtMostOnceFailsWhenMethodIsCalledTwice(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $mock->expects($this->atMost(1))->method('doSomething');

        $mock->doSomething();
        $mock->doSomething();

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked at most 1 time.
Expected invocation at most 1 time but it occurred 2 times.

EOT,
            $mock,
        );
    }

    public function testExpectationThatMethodIsCalledWithAnyParameterSucceedsWhenMethodIsCalledWithParameter(): void
    {
        $mock = $this->createMock(InterfaceWithReturnTypeDeclaration::class);

        $mock->expects($this->once())->method('doSomethingElse')->withAnyParameters();

        $mock->doSomethingElse(1);
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
     * With <code>$mock->expects($this->once())->method('one')->id($id);</code>,
     * we configure an expectation that one() is called once. This expectation is given the ID $id.
     *
     * With <code>$mock->expects($this->once())->method('two')->after($id);</code>,
     * we configure an expectation that two() is called once. However, this expectation will only be verified
     * if/after one() has been called.
     */
    public function testMethodCallCanBeExpectedContingentOnWhetherAnotherMethodWasPreviouslyCalled(): void
    {
        $id   = 'the-id';
        $mock = $this->createMock(InterfaceWithImplicitProtocol::class);

        $mock->expects($this->once())
            ->method('one')
            ->id($id);

        $mock->expects($this->once())
            ->method('two')
            ->after($id);

        $mock->one();
        $mock->two();
    }

    public function testContingentExpectationsAreNotEvaluatedUntilTheirConditionIsMet(): void
    {
        $id   = 'the-id';
        $mock = $this->createMock(InterfaceWithImplicitProtocol::class);

        $mock->expects($this->once())
            ->method('one')
            ->id($id);

        $mock->expects($this->once())
            ->method('two')
            ->after($id);

        $mock->two();
        $mock->one();
        $mock->two();
    }

    public function testContingentExpectationsAreEvaluatedWhenTheirConditionIsMet(): void
    {
        $id   = 'the-id';
        $mock = $this->createMock(InterfaceWithImplicitProtocol::class);

        $mock->expects($this->once())
            ->method('one')
            ->id($id);

        $mock->expects($this->once())
            ->method('two')
            ->after($id);

        $mock->two();
        $mock->one();

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "two" when invoked 1 time.
Method was expected to be called 1 time, actually called 0 times.

EOT,
            $mock,
        );
    }

    public function testExpectationCannotBeContingentOnExpectationThatHasNotBeenConfigured(): void
    {
        $mock = $this->createMock(InterfaceWithImplicitProtocol::class);

        $mock->expects($this->once())
            ->method('two')
            ->after('the-id');

        $this->assertThatMockObjectExpectationFails(
            'No builder found for match builder identification <the-id>',
            $mock,
            'two',
        );
    }

    public function testExpectationsCannotHaveDuplicateIds(): void
    {
        $id   = 'the-id';
        $mock = $this->createMock(InterfaceWithImplicitProtocol::class);

        $mock->expects($this->once())
            ->method('one')
            ->id($id);

        try {
            $mock->expects($this->once())
                ->method('one')
                ->id($id);
        } catch (MatcherAlreadyRegisteredException $e) {
            $this->assertSame('Matcher with id <the-id> is already registered', $e->getMessage());

            return;
        } finally {
            $this->resetMockObjects();
        }

        $this->fail();
    }

    #[IgnorePhpunitDeprecations]
    public function testExpectationsCanBeConfiguredOnTestStubs(): void
    {
        $mock = $this->createStub(AnInterface::class);

        $mock->expects($this->never())->method('doSomething');

        $this->assertTrue(true);
    }

    public function testWillReturnCallbackWithVariadicVariables(): void
    {
        $mock = $this->createMock(MethodWIthVariadicVariables::class);
        $mock->expects($this->once())->method('testVariadic')
            ->withAnyParameters()
            ->willReturnCallback(static fn ($string, ...$arguments) => [$string, ...$arguments]);

        $testData = ['foo', 'bar', 'biz' => 'kuz'];
        $actual   = $mock->testVariadic(...$testData);

        $this->assertSame($testData, $actual);
    }

    public function testExpectationsAreClonedWhenTestDoubleIsCloned(): void
    {
        $double = $this->createMock(InterfaceWithReturnTypeDeclaration::class);

        $double->expects($this->exactly(2))->method('doSomething');

        $clone = clone $double;

        $double->expects($this->once())->method('doSomethingElse')->willReturn(1);
        $clone->expects($this->once())->method('doSomethingElse')->willReturn(2);

        $this->assertFalse($double->doSomething());
        $this->assertFalse($clone->doSomething());
        $this->assertSame(1, $double->doSomethingElse(0));
        $this->assertSame(2, $clone->doSomethingElse(0));
    }

    /**
     * @param class-string $type
     */
    protected function createTestDouble(string $type): object
    {
        return $this->createMock($type);
    }

    private function assertThatMockObjectExpectationFails(string $expectationFailureMessage, MockObject $mock, string $methodName = '__phpunit_verify', array $arguments = []): void
    {
        try {
            call_user_func_array([$mock, $methodName], $arguments);
        } catch (ExpectationFailedException|MatchBuilderNotFoundException $e) {
            $this->assertSame($expectationFailureMessage, $e->getMessage());

            return;
        } finally {
            $this->resetMockObjects();
        }

        $this->fail();
    }

    private function resetMockObjects(): void
    {
        (new ReflectionProperty(TestCase::class, 'mockObjects'))->setValue($this, []);
    }
}
