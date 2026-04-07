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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Rule\MethodName;
use PHPUnit\Framework\MockObject\Rule\Parameters;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use ReflectionProperty;

#[CoversClass(InvocationHandler::class)]
#[Group('test-doubles')]
#[Small]
final class InvocationHandlerTest extends TestCase
{
    public function testSealingAlreadySealedHandlerReturnsEarly(): void
    {
        $handler = new InvocationHandler([], 'stdClass', false, false);

        $handler->seal(false);

        $this->assertTrue($handler->isSealed());

        $handler->seal(false);

        $this->assertTrue($handler->isSealed());
    }

    public function testSealingStubDoesNotAddMatchersForUnconfiguredMethods(): void
    {
        $stub = $this->createStub(AnInterface::class);

        $stub->method('doSomething')->willReturn(true)->seal();

        $this->assertTrue($stub->doSomething());
    }

    public function testConfiguredMethodNamesSkipsMatcherWithoutMethodNameRule(): void
    {
        $handler = new InvocationHandler([], 'stdClass', false, true);

        $matcher = new Matcher(new AnyInvokedCount, 'stdClass');

        $matchers = new ReflectionProperty(InvocationHandler::class, 'matchers');
        $matchers->setValue($handler, [$matcher]);

        $handler->seal(true);

        $this->assertTrue($handler->isSealed());
    }

    public function testHasMatcherWithParametersRuleForMethodNameReturnsFalseWhenNoMatcherExists(): void
    {
        $handler = new InvocationHandler([], 'stdClass', false, true);

        $matcher = new Matcher(new AnyInvokedCount, 'stdClass');

        $this->assertFalse($handler->hasMatcherWithParametersRuleForMethodName($matcher, 'doSomething'));
    }

    public function testHasMatcherWithParametersRuleForMethodNameSkipsMatcherWithoutMethodNameRule(): void
    {
        $handler = new InvocationHandler([], 'stdClass', false, true);

        $matcherWithoutMethodName = new Matcher(new AnyInvokedCount, 'stdClass');
        $matcherWithoutMethodName->setParametersRule(new Parameters([1]));

        $matchers = new ReflectionProperty(InvocationHandler::class, 'matchers');
        $matchers->setValue($handler, [$matcherWithoutMethodName]);

        $newMatcher = new Matcher(new AnyInvokedCount, 'stdClass');

        $this->assertFalse($handler->hasMatcherWithParametersRuleForMethodName($newMatcher, 'doSomething'));
    }

    public function testHasMatcherWithParametersRuleForMethodNameReturnsFalseWhenMatcherHasNoParametersRule(): void
    {
        $handler = new InvocationHandler([], 'stdClass', false, true);

        $existingMatcher = new Matcher(new AnyInvokedCount, 'stdClass');
        $existingMatcher->setMethodNameRule(new MethodName('doSomething'));

        $matchers = new ReflectionProperty(InvocationHandler::class, 'matchers');
        $matchers->setValue($handler, [$existingMatcher]);

        $newMatcher = new Matcher(new AnyInvokedCount, 'stdClass');

        $this->assertFalse($handler->hasMatcherWithParametersRuleForMethodName($newMatcher, 'doSomething'));
    }

    public function testHasMatcherWithParametersRuleForMethodNameReturnsTrueWhenAnotherMatcherHasParametersRule(): void
    {
        $handler = new InvocationHandler([], 'stdClass', false, true);

        $existingMatcher = new Matcher(new AnyInvokedCount, 'stdClass');
        $existingMatcher->setMethodNameRule(new MethodName('doSomething'));
        $existingMatcher->setParametersRule(new Parameters([1]));

        $matchers = new ReflectionProperty(InvocationHandler::class, 'matchers');
        $matchers->setValue($handler, [$existingMatcher]);

        $newMatcher = new Matcher(new AnyInvokedCount, 'stdClass');

        $this->assertTrue($handler->hasMatcherWithParametersRuleForMethodName($newMatcher, 'doSomething'));
    }

    public function testHasMatcherWithParametersRuleForMethodNameExcludesCurrentMatcher(): void
    {
        $handler = new InvocationHandler([], 'stdClass', false, true);

        $matcher = new Matcher(new AnyInvokedCount, 'stdClass');
        $matcher->setMethodNameRule(new MethodName('doSomething'));
        $matcher->setParametersRule(new Parameters([1]));

        $matchers = new ReflectionProperty(InvocationHandler::class, 'matchers');
        $matchers->setValue($handler, [$matcher]);

        $this->assertFalse($handler->hasMatcherWithParametersRuleForMethodName($matcher, 'doSomething'));
    }

    public function testHasMatcherWithParametersRuleForMethodNameReturnsFalseForDifferentMethod(): void
    {
        $handler = new InvocationHandler([], 'stdClass', false, true);

        $existingMatcher = new Matcher(new AnyInvokedCount, 'stdClass');
        $existingMatcher->setMethodNameRule(new MethodName('doSomething'));
        $existingMatcher->setParametersRule(new Parameters([1]));

        $matchers = new ReflectionProperty(InvocationHandler::class, 'matchers');
        $matchers->setValue($handler, [$existingMatcher]);

        $newMatcher = new Matcher(new AnyInvokedCount, 'stdClass');

        $this->assertFalse($handler->hasMatcherWithParametersRuleForMethodName($newMatcher, 'doSomethingElse'));
    }
}
