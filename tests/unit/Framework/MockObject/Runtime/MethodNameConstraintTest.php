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
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodNameConstraint::class)]
#[Group('test-doubles')]
#[Small]
final class MethodNameConstraintTest extends TestCase
{
    public function testHasMethodName(): void
    {
        $constraint = new MethodNameConstraint('foo');

        $this->assertSame('foo', $constraint->methodName());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $constraint = new MethodNameConstraint('foo');

        $this->assertSame('is "foo"', $constraint->toString());
    }

    public function testMatchesMatchingMethodNameCaseInsensitively(): void
    {
        $constraint = new MethodNameConstraint('foo');

        $this->assertTrue($constraint->evaluate('foo', returnResult: true));
        $this->assertTrue($constraint->evaluate('FOO', returnResult: true));
        $this->assertTrue($constraint->evaluate('Foo', returnResult: true));
        $this->assertTrue($constraint->evaluate('fOo', returnResult: true));
    }

    public function testDoesNotMatchDifferentMethodName(): void
    {
        $constraint = new MethodNameConstraint('foo');

        $this->assertFalse($constraint->evaluate('bar', returnResult: true));
    }
}
