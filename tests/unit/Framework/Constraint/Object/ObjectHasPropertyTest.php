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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ObjectHasProperty::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
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
        $this->expectExceptionMessageIs('Failed asserting that object of class "stdClass" has property "theProperty".');

        $constraint->evaluate(new stdClass);
    }

    public function testHandlesNonObjectsGracefully(): void
    {
        $constraint = new ObjectHasProperty('theProperty');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that "non-object" (string) has property "theProperty".');

        $constraint->evaluate('non-object');
    }

    public function testHandlesNonScalarNonObjectGracefully(): void
    {
        $constraint = new ObjectHasProperty('theProperty');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that "" (array) has property "theProperty".');

        $constraint->evaluate([]);
    }

    public function testCanBeRepresentedAsString(): void
    {
        $constraint = new ObjectHasProperty('theProperty');

        $this->assertSame('has property "theProperty"', $constraint->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new ObjectHasProperty('theProperty'));

        $this->assertSame('does not have property "theProperty"', $constraint->toString());

        $object              = new stdClass;
        $object->theProperty = 'value';

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that object of class "stdClass" does not have property "theProperty".');

        $constraint->evaluate($object);
    }

    public function testIsCountable(): void
    {
        $constraint = new ObjectHasProperty('theProperty');

        $this->assertCount(1, $constraint);
    }

    public function testReturnsAffirmativeStringInNonLogicalNotContext(): void
    {
        $this->assertSame(
            'has property "theProperty"',
            LogicalAnd::fromConstraints(new ObjectHasProperty('theProperty'))->toString(),
        );
    }
}
