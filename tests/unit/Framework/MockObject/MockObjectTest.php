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
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithCloneMethod;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertyWithSetHook;
use PHPUnit\TestFixture\MockObject\ExtendableReadonlyClassWithCloneMethod;
use PHPUnit\TestFixture\MockObject\InterfaceWithImplicitProtocol;
use PHPUnit\TestFixture\MockObject\InterfaceWithMethodThatExpectsObject;
use PHPUnit\TestFixture\MockObject\InterfaceWithPropertyWithSetHook;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;
use PHPUnit\TestFixture\MockObject\MethodWIthVariadicVariables;
use ReflectionProperty;
use stdClass;

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

    #[IgnorePhpunitDeprecations]
    public function testExpectationsCanBeConfiguredOnTestStubs(): void
    {
        $double = $this->createStub(AnInterface::class);

        $double->expects($this->never())->method('doSomething');

        $this->assertTrue(true);
    }

    public function testWillReturnCallbackWithVariadicVariables(): void
    {
        $double = $this->createMock(MethodWIthVariadicVariables::class);
        $double->expects($this->once())->method('testVariadic')
            ->withAnyParameters()
            ->willReturnCallback(static fn ($string, ...$arguments) => [$string, ...$arguments]);

        $testData = ['foo', 'bar', 'biz' => 'kuz'];
        $actual   = $double->testVariadic(...$testData);

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

    #[IgnorePhpunitDeprecations]
    public function testCloningOfObjectsPassedAsArgumentCanBeEnabled(): void
    {
        $object = new stdClass;

        $double = $this->getMockBuilder(InterfaceWithMethodThatExpectsObject::class)
            ->enableArgumentCloning()
            ->getMock();

        $double->method('doSomething')->willReturnArgument(0);

        $this->assertNotSame($object, $double->doSomething($object));
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
    #[RequiresPhp('^8.3')]
    public function testOriginalCloneMethodCanOptionallyBeCalledWhenTestDoubleObjectOfReadonlyClassIsCloned(): void
    {
        $double = $this->getMockBuilder(ExtendableReadonlyClassWithCloneMethod::class)->enableOriginalClone()->getMock();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(ExtendableReadonlyClassWithCloneMethod::class . '::__clone');

        clone $double;
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
