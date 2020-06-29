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

use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\MockObject\Rule\MethodName;
use PHPUnit\Framework\MockObject\Rule\ParametersRule;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \PHPUnit\Framework\MockObject\Matcher
 */
class MatcherTest extends TestCase
{
    public function testParameterRuleIsAppliedToInvocation(): void
    {
        $invocationMatcher = $this->createStub(InvocationOrder::class);
        $invocation        = new Invocation('Foo', 'bar', [], 'void', new stdClass);

        $parameterRule = $this->createMock(ParametersRule::class);
        $parameterRule->expects($this->once())
            ->method('apply')
            ->with($invocation);

        $matcher = new Matcher($invocationMatcher);
        $matcher->setMethodNameRule(new MethodName('bar'));
        $matcher->setParametersRule($parameterRule);

        $matcher->invoked($invocation);
    }

    public function testParametersRuleTriggersFailOfInvocation(): void
    {
        $invocationMatcher = $this->createStub(InvocationOrder::class);
        $invocation        = new Invocation('Foo', 'bar', [], 'void', new stdClass);

        $parameterRule = $this->createStub(ParametersRule::class);
        $parameterRule->method('apply')
            ->willThrowException(new ExpectationFailedException('rule is always violated.'));

        $matcher = new Matcher($invocationMatcher);
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
        $invocation = new Invocation('Foo', 'bar', [], 'void', new stdClass);
        $matcher    = new Matcher($invocationMatcher);
        $matcher->setMethodNameRule(new MethodName('bar'));

        $parameterRule = $this->createStub(ParametersRule::class);
        $parameterRule->method('apply')
            ->willThrowException(new Exception('This method should not have been called.'));
        $matcher->setParametersRule($parameterRule);

        $this->assertTrue($matcher->matches($invocation));
    }

    public function testStubIsNotInvokedIfParametersRuleIsViolated(): void
    {
        $invocationMatcher = $this->createStub(InvocationOrder::class);
        $invocation        = new Invocation('Foo', 'bar', [], 'void', new stdClass);

        $stub = $this->createMock(Stub\Stub::class);
        $stub->expects($this->never())
            ->method('invoke');

        $parameterRule = $this->createStub(ParametersRule::class);
        $parameterRule->method('apply')
            ->willThrowException(new ExpectationFailedException('rule is always violated.'));

        $matcher = new Matcher($invocationMatcher);
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
        $invocation        = new Invocation('Foo', 'bar', [], 'void', new stdClass);

        $stub = $this->createMock(Stub\Stub::class);
        $stub->expects($this->once())
            ->method('invoke')
            ->with($invocation);

        $parameterRule = $this->createStub(ParametersRule::class);

        $matcher = new Matcher($invocationMatcher);
        $matcher->setMethodNameRule(new MethodName('bar'));
        $matcher->setParametersRule($parameterRule);
        $matcher->setStub($stub);

        $matcher->invoked($invocation);
    }
}
