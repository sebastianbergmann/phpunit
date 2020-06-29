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

use AnInterface;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\MockObject\Rule\MethodName;
use PHPUnit\Framework\MockObject\Rule\ParametersRule;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Framework\MockObject\Matcher
 */
class MatcherTest extends TestCase
{
    public function testParameterRuleIsAppliedToInvocation(): void
    {
        $invocationMatcher = $this->createStub(InvocationOrder::class);
        $invocation        = new Invocation('Foo', 'bar', [], 'void', new \stdClass);

        $parameterRule = $this->createMock(ParametersRule::class);
        $parameterRule->expects($this->once())
            ->method('apply')
            ->with($invocation);

        $matcher = new Matcher($invocationMatcher, false);
        $matcher->setMethodNameRule(new MethodName('bar'));
        $matcher->setParametersRule($parameterRule);

        $matcher->invoked($invocation);
    }

    public function testParametersRuleTriggersFailOfInvocation(): void
    {
        $invocationMatcher = $this->createStub(InvocationOrder::class);
        $invocation        = new Invocation('Foo', 'bar', [], 'void', new \stdClass);

        $parameterRule = $this->createStub(ParametersRule::class);
        $parameterRule->method('apply')
            ->willThrowException(new ExpectationFailedException('rule is always violated.'));

        $matcher = new Matcher($invocationMatcher, false);
        $matcher->setMethodNameRule(new MethodName('bar'));
        $matcher->setParametersRule($parameterRule);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("Expectation failed for method name is \"bar\" when \nrule is always violated.");
        $matcher->invoked($invocation);
    }

    public function testParameterRuleDoesNotInfluenceMatches(): void
    {
        $invocationMatcher = $this->createStub(InvocationOrder::class);
        $invocationMatcher->method('matches')
            ->willReturn(true);
        $invocation = new Invocation('Foo', 'bar', [], 'void', new \stdClass);
        $matcher    = new Matcher($invocationMatcher, false);
        $matcher->setMethodNameRule(new MethodName('bar'));

        $parameterRule = $this->createStub(ParametersRule::class);
        $parameterRule->method('apply')
            ->willThrowException(new \Exception('This method should not have been called.'));
        $matcher->setParametersRule($parameterRule);

        $this->assertTrue($matcher->matches($invocation));
    }

    public function testStubIsNotInvokedIfParametersRuleIsViolated(): void
    {
        $invocationMatcher = $this->createStub(InvocationOrder::class);
        $invocation        = new Invocation('Foo', 'bar', [], 'void', new \stdClass);

        $stub = $this->createMock(Stub\Stub::class);
        $stub->expects($this->never())
            ->method('invoke');

        $parameterRule = $this->createStub(ParametersRule::class);
        $parameterRule->method('apply')
            ->willThrowException(new ExpectationFailedException('rule is always violated.'));

        $matcher = new Matcher($invocationMatcher, false);
        $matcher->setMethodNameRule(new MethodName('bar'));
        $matcher->setParametersRule($parameterRule);
        $matcher->setStub($stub);

        try {
            $matcher->invoked($invocation);
        } catch (ExpectationFailedException $e) {
        }
    }

    public function testStubIsInvokedIfAllMatchersAndRulesApply(): void
    {
        $invocationMatcher = $this->createStub(InvocationOrder::class);
        $invocation        = new Invocation('Foo', 'bar', [], 'void', new \stdClass);

        $stub = $this->createMock(Stub\Stub::class);
        $stub->expects($this->once())
            ->method('invoke')
            ->with($invocation);

        $parameterRule = $this->createStub(ParametersRule::class);

        $matcher = new Matcher($invocationMatcher, false);
        $matcher->setMethodNameRule(new MethodName('bar'));
        $matcher->setParametersRule($parameterRule);
        $matcher->setStub($stub);

        $matcher->invoked($invocation);
    }

    /**
     * @return array[]
     */
    public function argumentsStrictTypesProvider(): array
    {
        return [
            [
                new Invocation(
                    'Foo',
                    'nonExistingMethod',
                    [],
                    'void',
                    new class {
                    }
                ),
                \ReflectionException::class,
            ],
            [
                new Invocation(
                    'Foo',
                    'emptyMethod',
                    [],
                    'void',
                    new class {
                        public function emptyMethod(): void
                        {
                        }
                    }
                ),
                null,
            ],
            [
                new Invocation(
                    'Foo',
                    'emptyMethod',
                    ['value'],
                    'void',
                    new class {
                        public function emptyMethod(): void
                        {
                        }
                    }
                ),
                RuntimeException::class,
            ],
            [
                new Invocation(
                    'Foo',
                    'methodAcceptingInt',
                    ['123'],
                    'void',
                    new class {
                        public function methodAcceptingInt(int $value): void
                        {
                        }
                    }
                ),
                RuntimeException::class,
            ],
            [
                new Invocation(
                    'Foo',
                    'methodAcceptingInt',
                    [123],
                    'void',
                    new class {
                        public function methodAcceptingInt(int $value): void
                        {
                        }
                    }
                ),
                null,
            ],
            [
                new Invocation(
                    'Foo',
                    'methodAcceptingAnInterface',
                    [
                        new class {
                        },
                    ],
                    'void',
                    new class {
                        public function methodAcceptingAnInterface(AnInterface $value): void
                        {
                        }
                    }
                ),
                RuntimeException::class,
            ],
            [
                new Invocation(
                    'Foo',
                    'methodAcceptingAnInterface',
                    [
                        new class implements AnInterface {
                            public function doSomething(): void
                            {
                            }
                        },
                    ],
                    'void',
                    new class {
                        public function methodAcceptingAnInterface(AnInterface $value): void
                        {
                        }
                    }
                ),
                null,
            ],
            [
                new Invocation(
                    'Foo',
                    'methodWithDefaultValue',
                    [],
                    'void',
                    new class {
                        public function methodWithDefaultValue(bool $value = true): void
                        {
                        }
                    }
                ),
                null,
            ],
            [
                new Invocation(
                    'Foo',
                    'nonTypedMethod',
                    ['anything'],
                    'void',
                    new class {
                        public function nonTypedMethod($value): void
                        {
                        }
                    }
                ),
                null,
            ],
            [
                new Invocation(
                    'Foo',
                    'methodAcceptingBoolInt',
                    [true, 123],
                    'void',
                    new class {
                        public function methodAcceptingBoolInt(
                            bool $argument1,
                            int $argument2
                        ): void {
                        }
                    }
                ),
                null,
            ],
            [
                new Invocation(
                    'Foo',
                    'methodAcceptingBoolInt',
                    [123, true],
                    'void',
                    new class {
                        public function methodAcceptingBoolInt(
                            bool $argument1,
                            int $argument2
                        ): void {
                        }
                    }
                ),
                RuntimeException::class,
            ],
            [
                new Invocation(
                    'Foo',
                    'methodAcceptingBoolInt',
                    [true, 123, 'string'],
                    'void',
                    new class {
                        public function methodAcceptingBoolInt(
                            bool $argument1,
                            int $argument2
                        ): void {
                        }
                    }
                ),
                RuntimeException::class,
            ],
        ];
    }

    /**
     * @dataProvider argumentsStrictTypesProvider
     *
     * @throws \Exception
     */
    public function testStrictTypesCheck(
        Invocation $invocation,
        ?string $expectedException
    ): void {
        $invocationMatcher = $this->createStub(InvocationOrder::class);

        $matcher = new Matcher($invocationMatcher, false);
        $matcher->setMethodNameRule(new MethodName('bar'));
        $matcher->invoked($invocation);

        if ($expectedException) {
            $this->expectException($expectedException);
        } else {
            $this->addToAssertionCount(1);
        }

        $matcher = new Matcher($invocationMatcher, true);
        $matcher->setMethodNameRule(new MethodName('bar'));
        $matcher->invoked($invocation);
    }
}
