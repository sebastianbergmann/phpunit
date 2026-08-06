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
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;

#[CoversClass(MethodName::class)]
#[Group('test-doubles')]
#[Small]
final class MethodNameTest extends TestCase
{
    public function testCanBeRepresentedAsStringWhenItIsConfiguredWithMethodName(): void
    {
        $rule = new MethodName('doSomething');

        $this->assertSame('method name is "doSomething"', $rule->toString());
    }

    public function testCanBeRepresentedAsStringWhenItIsConfiguredWithConstraint(): void
    {
        $rule = new MethodName(new StringStartsWith('doSome'));

        $this->assertSame('method name starts with "doSome"', $rule->toString());
    }

    public function testDescribesFailureUsingMethodNameWhenItIsConfiguredWithMethodName(): void
    {
        $rule = new MethodName('doSomething');

        $this->assertSame(
            AnInterface::class . '::doSomething()',
            $rule->failureDescription(AnInterface::class),
        );
    }

    public function testDescribesFailureUsingConstraintWhenItIsConfiguredWithConstraint(): void
    {
        $rule = new MethodName(new StringStartsWith('doSome'));

        $this->assertSame(
            'method name starts with "doSome"',
            $rule->failureDescription(AnInterface::class),
        );
    }

    public function testMatchesMethodName(): void
    {
        $rule = new MethodName('doSomething');

        $this->assertTrue($rule->matchesName('doSomething'));
        $this->assertFalse($rule->matchesName('doSomethingElse'));
    }
}
