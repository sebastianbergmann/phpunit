<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Rule;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\StubInternal;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;

#[CoversClass(AnyInvokedCount::class)]
#[Group('test-doubles')]
#[Small]
final class AnyInvokedCountTest extends TestCase
{
    public function testCanBeRepresentedAsString(): void
    {
        $rule = new AnyInvokedCount;

        $this->assertSame('invoked zero or more times', $rule->toString());
    }

    public function testMatchesEveryInvocation(): void
    {
        $double = $this->createStub(AnInterface::class);

        $this->assertInstanceOf(StubInternal::class, $double);

        $rule = new AnyInvokedCount;

        $this->assertTrue(
            $rule->matches(
                new Invocation(AnInterface::class, 'doSomething', [], 'bool', $double),
            ),
        );
    }

    public function testCanBeVerifiedWithoutInvocation(): void
    {
        $rule = new AnyInvokedCount;

        $rule->verify();

        $this->assertSame(0, $rule->numberOfInvocations());
    }
}
