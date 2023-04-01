<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ObjectHasProperty::class)]
#[Small]
final class ObjectHasPropertyTest extends TestCase
{
    public function testCanBeEvaluated(): void
    {
        $constraint = new ObjectHasProperty('theProperty');

        $objectWithProperty              = new stdClass;
        $objectWithProperty->theProperty = 'value';

        $this->assertTrue($constraint->evaluate($objectWithProperty, returnResult: true));
        $this->assertFalse($constraint->evaluate(new stdClass, returnResult: true));
        $this->assertFalse($constraint->evaluate(null, returnResult: true));

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that object of class "stdClass" has property "theProperty".');

        $constraint->evaluate(new stdClass);
    }

    public function testHandlesNonObjectsGracefully(): void
    {
        $constraint = new ObjectHasProperty('theProperty');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that "non-object" (string) has property "theProperty".');

        $constraint->evaluate('non-object');
    }

    public function testCanBeRepresentedAsString(): void
    {
        $constraint = new ObjectHasProperty('theProperty');

        $this->assertSame('has property "theProperty"', $constraint->toString());
    }

    public function testIsCountable(): void
    {
        $constraint = new ObjectHasProperty('theProperty');

        $this->assertCount(1, $constraint);
    }
}
