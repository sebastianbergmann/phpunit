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
use PHPUnit\Framework\TestFailure;
use PHPUnit\TestFixture\ClassWithNonPublicAttributes;
use stdClass;

/**
 * @small
 */
final class ClassHasAttributeTest extends ConstraintTestCase
{
    public function testConstraintClassHasAttribute(): void
    {
        $constraint = new ClassHasAttribute(
            'privateAttribute'
        );

        $this->assertTrue($constraint->evaluate(ClassWithNonPublicAttributes::class, '', true));
        $this->assertFalse($constraint->evaluate(stdClass::class, '', true));
        $this->assertEquals('has attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(stdClass::class);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that class "stdClass" has attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintClassHasAttribute2(): void
    {
        $constraint = new ClassHasAttribute(
            'privateAttribute'
        );

        try {
            $constraint->evaluate(stdClass::class, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
custom message
Failed asserting that class "stdClass" has attribute "privateAttribute".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
