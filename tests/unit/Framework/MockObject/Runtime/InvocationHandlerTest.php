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
        $handler = new InvocationHandler([], false, false);

        $handler->seal(false);

        $this->assertTrue($handler->isSealed());

        // Second call hits the early return (line 190)
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
        $handler = new InvocationHandler([], false, true);

        $matcher = new Matcher(new AnyInvokedCount);

        $matchers = new ReflectionProperty(InvocationHandler::class, 'matchers');
        $matchers->setValue($handler, [$matcher]);

        // seal(true) calls configuredMethodNames(), which iterates matchers;
        // the injected matcher has no method name rule → hits the `continue` branch
        $handler->seal(true);

        $this->assertTrue($handler->isSealed());
    }
}
