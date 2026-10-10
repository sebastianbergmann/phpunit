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
use Exception;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithCloneMethod;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertyWithCovariantSetHook;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertyWithGetHook;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertyWithSetHook;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithVirtualPropertyWithSetHook;
use PHPUnit\TestFixture\MockObject\ExtendableReadonlyClassWithCloneMethod;
use PHPUnit\TestFixture\MockObject\InterfaceWithImplicitProtocol;
use PHPUnit\TestFixture\MockObject\InterfaceWithMethodThatHasDefaultParameterValues;
use PHPUnit\TestFixture\MockObject\InterfaceWithPropertyWithSetHook;
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
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->never())->method('doSomething');
    }

    public function testExpectationThatMethodIsNeverCalledFailsWhenMethodIsCalled(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->never())->method('doSomething');

        $this->assertThatMockObjectExpectationFails(
            AnInterface::class . '::doSomething(): bool was not expected to be called.',
            $double,
            'doSomething',
        );
    }

    #[DoesNotPerformAssertions]
    public function testExpectationThatMethodIsCalledZeroOrMoreTimesSucceedsWhenMethodIsNotCalled(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->any())->method('doSomething');
    }

    #[DoesNotPerformAssertions]
    public function testExpectationThatMethodIsCalledZeroOrMoreTimesSucceedsWhenMethodIsCalledOnce(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->any())->method('doSomething');

        $double->doSomething();
    }

    public function testExpectationThatMethodIsCalledOnceSucceedsWhenMethodIsCalledOnce(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->once())->method('doSomething');

        $double->doSomething();
    }

    public function testExpectationThatMethodIsCalledOnceFailsWhenMethodIsNeverCalled(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->once())->method('doSomething');

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 1 time.
Method was expected to be called 1 time, actually called 0 times.

EOT,
            $double,
        );
    }

    public function testExpectationThatMethodIsCalledOnceFailsWhenMethodIsCalledMoreThanOnce(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->once())->method('doSomething');

        $double->doSomething();

        $this->assertThatMockObjectExpectationFails(
            AnInterface::class . '::doSomething(): bool was not expected to be called more than once.',
            $double,
            'doSomething',
        );
    }

    public function testExpectationThatMethodIsCalledAtLeastOnceSucceedsWhenMethodIsCalledOnce(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->atLeastOnce())->method('doSomething');

        $double->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtLeastOnceSucceedsWhenMethodIsCalledTwice(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->atLeastOnce())->method('doSomething');

        $double->doSomething();
        $double->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtLeastTwiceSucceedsWhenMethodIsCalledTwice(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->atLeast(2))->method('doSomething');

        $double->doSomething();
        $double->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtLeastTwiceSucceedsWhenMethodIsCalledThreeTimes(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->atLeast(2))->method('doSomething');

        $double->doSomething();
        $double->doSomething();
        $double->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtLeastOnceFailsWhenMethodIsNotCalled(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->atLeastOnce())->method('doSomething');

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked at least once.
Expected invocation at least once but it never occurred.

EOT,
            $double,
        );
    }

    public function testExpectationThatMethodIsCalledAtLeastTwiceFailsWhenMethodIsCalledOnce(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->atLeast(2))->method('doSomething');

        $double->doSomething();

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked at least 2 times.
Expected invocation at least 2 times but it occurred 1 time.

EOT,
            $double,
        );
    }

    public function testExpectationThatMethodIsCalledTwiceSucceedsWhenMethodIsCalledTwice(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->exactly(2))->method('doSomething');

        $double->doSomething();
        $double->doSomething();
    }

    public function testExpectationThatMethodIsCalledTwiceFailsWhenMethodIsNeverCalled(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->exactly(2))->method('doSomething');

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 2 times.
Method was expected to be called 2 times, actually called 0 times.

EOT,
            $double,
        );
    }

    public function testExpectationThatMethodIsCalledTwiceFailsWhenMethodIsCalledOnce(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->exactly(2))->method('doSomething');

        $double->doSomething();

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 2 times.
Method was expected to be called 2 times, actually called 1 time.

EOT,
            $double,
        );
    }

    public function testExpectationThatMethodIsCalledTwiceFailsWhenMethodIsCalledThreeTimes(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->exactly(2))->method('doSomething');

        $double->doSomething();
        $double->doSomething();

        $this->assertThatMockObjectExpectationFails(
            AnInterface::class . '::doSomething(): bool was not expected to be called more than 2 times.',
            $double,
            'doSomething',
        );
    }

    public function testExpectationThatMethodIsCalledAtMostOnceSucceedsWhenMethodIsNeverCalled(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->atMost(1))->method('doSomething');
    }

    public function testExpectationThatMethodIsCalledAtMostOnceSucceedsWhenMethodIsCalledOnce(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->atMost(1))->method('doSomething');

        $double->doSomething();
    }

    public function testExpectationThatMethodIsCalledAtMostOnceFailsWhenMethodIsCalledTwice(): void
    {
        $double = $this->createMock(AnInterface::class);

        $double->expects($this->atMost(1))->method('doSomething');

        $double->doSomething();
        $double->doSomething();

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked at most 1 time.
Expected invocation at most 1 time but it occurred 2 times.

EOT,
            $double,
        );
    }

    public function testExpectationThatMethodIsCalledWithAnyParameterSucceedsWhenMethodIsCalledWithParameter(): void
    {
        $double = $this->createMock(InterfaceWithReturnTypeDeclaration::class);

        $double->expects($this->once())->method('doSomethingElse')->withAnyParameters();

        $double->doSomethingElse(1);
    }

    public function testExpectationThatMethodIsCalledWithParameterSucceedsWhenMethodIsCalledWithExpectedParameter(): void
    {
        $double = $this->createMock(InterfaceWithReturnTypeDeclaration::class);

        $double->expects($this->once())->method('doSomethingElse')->with(1);

        $double->doSomethingElse(1);
    }

    public function testExpectationThatMethodIsCalledWithParameterFailsWhenMethodIsCalledButWithUnexpectedParameter(): void
    {
        $double = $this->createMock(InterfaceWithReturnTypeDeclaration::class);

        $double->expects($this->once())->method('doSomethingElse')->with(1);

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomethingElse" when invoked 1 time
Parameter 0 for invocation PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration::doSomethingElse(0): int does not match expected value.
Failed asserting that 0 matches expected 1.
EOT,
            $double,
            'doSomethingElse',
            [0],
        );
    }

    public function testExpectationThatMethodIsCalledWithNamedParameterSucceedsWhenMethodIsCalledWithExpectedValueForThatParameter(): void
    {
        $double = $this->createMock(InterfaceWithMethodThatHasDefaultParameterValues::class);

        $double->expects($this->once())->method('doSomething')->with(b: 7);

        $double->doSomething(1, 7);
    }

    public function testExpectationThatMethodIsCalledWithNamedParameterFailsWhenMethodIsCalledWithUnexpectedValueForThatParameter(): void
    {
        $double = $this->createMock(InterfaceWithMethodThatHasDefaultParameterValues::class);

        $double->expects($this->once())->method('doSomething')->with(b: 7);

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 1 time
Parameter 1 for invocation PHPUnit\TestFixture\MockObject\InterfaceWithMethodThatHasDefaultParameterValues::doSomething(7, 3): int does not match expected value.
Failed asserting that 3 matches expected 7.
EOT,
            $double,
            'doSomething',
            [7, 3],
        );
    }

    public function testExpectationThatMethodIsCalledWithPositionalAndNamedParametersSucceedsWhenNamedParameterHasExpectedDefaultValue(): void
    {
        $double = $this->createMock(InterfaceWithMethodThatHasDefaultParameterValues::class);

        $double->expects($this->once())->method('doSomething')->with(5, b: 1);

        $double->doSomething(5);
    }

    public function testExpectationThatMethodIsCalledWithNamedParameterFailsWhenMethodHasNoParameterWithThatName(): void
    {
        $double = $this->createMock(InterfaceWithMethodThatHasDefaultParameterValues::class);

        $double->expects($this->once())->method('doSomething')->with(c: 7);

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 1 time
Unknown named parameter $c for invocation PHPUnit\TestFixture\MockObject\InterfaceWithMethodThatHasDefaultParameterValues::doSomething(7, 1): int.
EOT,
            $double,
            'doSomething',
            [7],
        );
    }

    public function testExpectationThatMethodIsCalledWithNamedParameterFailsWhenParameterWithThatNameIsAlsoConfiguredByPosition(): void
    {
        $double = $this->createMock(InterfaceWithMethodThatHasDefaultParameterValues::class);

        $double->expects($this->once())->method('doSomething')->with(7, a: 7);

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "doSomething" when invoked 1 time
Named parameter $a overwrites previous argument for invocation PHPUnit\TestFixture\MockObject\InterfaceWithMethodThatHasDefaultParameterValues::doSomething(7, 1): int.
EOT,
            $double,
            'doSomething',
            [7],
        );
    }

    public function testExpectationThatMethodIsCalledWithNamedParameterSucceedsWhenVariadicParameterCollectsNamedArgumentWithExpectedValue(): void
    {
        $double = $this->createMock(MethodWIthVariadicVariables::class);

        $double->expects($this->once())->method('testVariadic')->with('foo', biz: 'kuz');

        $double->testVariadic('foo', biz: 'kuz');
    }

    public function testExpectationThatMethodIsCalledWithNamedParameterFailsWhenVariadicParameterCollectsNamedArgumentWithUnexpectedValue(): void
    {
        $double = $this->createMock(MethodWIthVariadicVariables::class);

        $double->expects($this->once())->method('testVariadic')->with('foo', biz: 'kuz');

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "testVariadic" when invoked 1 time
Parameter biz for invocation PHPUnit\TestFixture\MockObject\MethodWIthVariadicVariables::testVariadic('foo', 'bar'): array does not match expected value.
Failed asserting that two strings are equal.
EOT,
            $double,
            'testVariadic',
            ['foo', 'biz' => 'bar'],
        );
    }

    public function testExpectationThatMethodIsCalledWithNamedParameterFailsWhenVariadicParameterDoesNotCollectNamedArgumentWithThatName(): void
    {
        $double = $this->createMock(MethodWIthVariadicVariables::class);

        $double->expects($this->once())->method('testVariadic')->with('foo', biz: 'kuz');

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "testVariadic" when invoked 1 time
Parameter count for invocation PHPUnit\TestFixture\MockObject\MethodWIthVariadicVariables::testVariadic('foo', 'kuz'): array is too low.
EOT,
            $double,
            'testVariadic',
            ['foo', 'baz' => 'kuz'],
        );
    }

    /**
     * With <code>$double->expects($this->once())->method('one')->id($id);</code>,
     * we configure an expectation that one() is called once. This expectation is given the ID $id.
     *
     * With <code>$double->expects($this->once())->method('two')->after($id);</code>,
     * we configure an expectation that two() is called once. However, this expectation will only be verified
     * if/after one() has been called.
     */
    public function testMethodCallCanBeExpectedContingentOnWhetherAnotherMethodWasPreviouslyCalled(): void
    {
        $id     = 'the-id';
        $double = $this->createMock(InterfaceWithImplicitProtocol::class);

        $double->expects($this->once())
            ->method('one')
            ->id($id);

        $double->expects($this->once())
            ->method('two')
            ->after($id);

        $double->one();
        $double->two();
    }

    public function testContingentExpectationsAreNotEvaluatedUntilTheirConditionIsMet(): void
    {
        $id     = 'the-id';
        $double = $this->createMock(InterfaceWithImplicitProtocol::class);

        $double->expects($this->once())
            ->method('one')
            ->id($id);

        $double->expects($this->once())
            ->method('two')
            ->after($id);

        $double->two();
        $double->one();
        $double->two();
    }

    public function testContingentExpectationsAreEvaluatedWhenTheirConditionIsMet(): void
    {
        $id     = 'the-id';
        $double = $this->createMock(InterfaceWithImplicitProtocol::class);

        $double->expects($this->once())
            ->method('one')
            ->id($id);

        $double->expects($this->once())
            ->method('two')
            ->after($id);

        $double->two();
        $double->one();

        $this->assertThatMockObjectExpectationFails(
            <<<'EOT'
Expectation failed for method name is "two" when invoked 1 time.
Method was expected to be called 1 time, actually called 0 times.

EOT,
            $double,
        );
    }

    public function testExpectationCannotBeContingentOnExpectationThatHasNotBeenConfigured(): void
    {
        $double = $this->createMock(InterfaceWithImplicitProtocol::class);

        $double->expects($this->once())
            ->method('two')
            ->after('the-id');

        $this->assertThatMockObjectExpectationFails(
            'No builder found for match builder identification <the-id>',
            $double,
            'two',
        );
    }

    public function testExpectationsCannotHaveDuplicateIds(): void
    {
        $id     = 'the-id';
        $double = $this->createMock(InterfaceWithImplicitProtocol::class);

        $double->expects($this->once())
            ->method('one')
            ->id($id);

        try {
            $double->expects($this->once())
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

    public function testWillReturnCallbackWithVariadicVariables(): void
    {
        $double = $this->createMock(MethodWIthVariadicVariables::class);
        $double->expects($this->once())->method('testVariadic')
            ->withAnyParameters()
            ->willReturnCallback(static fn (string $string, string ...$arguments) => [$string, ...$arguments]);

        $testData = ['foo', 'bar', 'biz' => 'kuz'];
        $actual   = $double->testVariadic(...$testData);

        $this->assertSame($testData, $actual);
    }

    public function testAssertionFailureInCallbackIsReportedDuringVerification(): void
    {
        $double = $this->createMock(InterfaceWithReturnTypeDeclaration::class);

        $double->expects($this->once())
            ->method('doSomethingElse')
            ->willReturnCallback(function (int $x): int
            {
                $this->assertSame(1, $x);

                return $x;
            });

        try {
            $double->doSomethingElse(2);
        } catch (Exception) {
        }

        $this->assertThatMockObjectExpectationFails(
            'Failed asserting that 2 is identical to 1.',
            $double,
        );
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

    #[RequiresMethod(ReflectionProperty::class, 'isFinal')]
    public function testExpectationCanBeConfiguredForSetHookForPropertyOfInterface(): void
    {
        $double = $this->createTestDouble(InterfaceWithPropertyWithSetHook::class);

        $double->expects($this->once())->method(PropertyHook::set('property'))->with('value');

        $double->property = 'value';
    }

    #[RequiresMethod(ReflectionProperty::class, 'isFinal')]
    public function testExpectationCanBeConfiguredForSetHookForPropertyOfExtendableClass(): void
    {
        $double = $this->createTestDouble(ExtendableClassWithPropertyWithSetHook::class);

        $double->expects($this->once())->method(PropertyHook::set('property'))->with('value');

        $double->property = 'value';
    }

    #[RequiresMethod(ReflectionProperty::class, 'isFinal')]
    public function testExpectationCanBeConfiguredForSetHookForVirtualPropertyOfExtendableClass(): void
    {
        $double = $this->createTestDouble(ExtendableClassWithVirtualPropertyWithSetHook::class);

        $double->expects($this->once())->method(PropertyHook::set('property'))->with('value');

        $double->property = 'value';
    }

    #[RequiresMethod(ReflectionProperty::class, 'isFinal')]
    public function testExpectationCanBeConfiguredForSetHookForNonVirtualPropertyWithGetHookOfExtendableClass(): void
    {
        $double = $this->createTestDouble(ExtendableClassWithPropertyWithGetHook::class);

        $double->expects($this->once())->method(PropertyHook::set('property'))->with('value');

        $double->property = 'value';
    }

    #[RequiresMethod(ReflectionProperty::class, 'isFinal')]
    public function testExpectationCanBeConfiguredForCovariantSetHookForPropertyOfExtendableClass(): void
    {
        $double = $this->createTestDouble(ExtendableClassWithPropertyWithCovariantSetHook::class);

        $double->expects($this->once())->method(PropertyHook::set('property'))->with('0');

        $double->property = '0';
    }

    #[TestDox('__toString() method returns empty string when return value generation is disabled and no return value is configured')]
    public function testToStringMethodReturnsEmptyStringWhenReturnValueGenerationIsDisabledAndNoReturnValueIsConfigured(): void
    {
        $double = $this->getMockBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->disableAutoReturnValueGeneration()
            ->getMock();

        $this->assertSame('', $double->__toString());
    }

    public function testMethodDoesNotReturnValueWhenReturnValueGenerationIsDisabledAndNoReturnValueIsConfigured(): void
    {
        $double = $this->getMockBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->disableAutoReturnValueGeneration()
            ->getMock();

        $this->expectException(ReturnValueNotConfiguredException::class);
        $this->expectExceptionMessage('No return value is configured for ' . InterfaceWithReturnTypeDeclaration::class . '::doSomething() and return value generation is disabled');

        $double->doSomething();
    }

    #[TestDox('Original __clone() method can optionally be called when test double object is cloned')]
    public function testOriginalCloneMethodCanOptionallyBeCalledWhenTestDoubleObjectIsCloned(): void
    {
        $double = $this->getMockBuilder(ExtendableClassWithCloneMethod::class)->enableOriginalClone()->getMock();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(ExtendableClassWithCloneMethod::class . '::__clone');

        clone $double;
    }

    #[TestDox('Original __clone() method can optionally be called when test double object is cloned (readonly class)')]
    public function testOriginalCloneMethodCanOptionallyBeCalledWhenTestDoubleObjectOfReadonlyClassIsCloned(): void
    {
        $double = $this->getMockBuilder(ExtendableReadonlyClassWithCloneMethod::class)->enableOriginalClone()->getMock();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(ExtendableReadonlyClassWithCloneMethod::class . '::__clone');

        clone $double;
    }

    #[DoesNotPerformAssertions]
    #[TestDox('with() can be used without expects() and is not verified when the method is not called')]
    public function testWithCanBeUsedWithoutExpectsAndIsNotVerifiedWhenTheMethodIsNotCalled(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomethingElse')->with(1)->willReturn(2);
    }

    #[TestDox('with() can be used without expects() and is verified when the method is called')]
    public function testWithCanBeUsedWithoutExpectsAndIsVerifiedWhenTheMethodIsCalled(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomethingElse')->with(1)->willReturn(2);

        $this->expectException(ExpectationFailedException::class);

        $double->doSomethingElse(0);
    }

    /**
     * @param class-string $type
     */
    protected function createTestDouble(string $type): object
    {
        return $this->createMock($type);
    }

    private function assertThatMockObjectExpectationFails(string $expectationFailureMessage, MockObject $double, string $methodName = '__phpunit_verify', array $arguments = []): void
    {
        try {
            call_user_func_array([$double, $methodName], $arguments);
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
