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

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\TestFixture\ClassWithNonPublicAttributes;

/**
 * @small
 */
final class ObjectHasPropertyTest extends ConstraintTestCase
{
    public function testConstraintObjectHasAttribute(): void
    {
        $constraint = new ObjectHasProperty('privateAttribute');

        $this->assertTrue($constraint->evaluate(new ClassWithNonPublicAttributes, '', true));
        $this->assertFalse($constraint->evaluate(new \stdClass, '', true));
        $this->assertEquals('has property "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(<<<'EOF'
            Failed asserting that object of class "stdClass" has property "privateAttribute".
            EOF
        );
        $constraint->evaluate(new \stdClass);
    }

    public function testConstraintObjectHasAttribute2(): void
    {
        $constraint = new ObjectHasProperty('privateAttribute');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(<<<EOF
            custom message\nFailed asserting that object of class "stdClass" has property "privateAttribute".
            EOF
        );
        $constraint->evaluate(new \stdClass, 'custom message');
    }
}
