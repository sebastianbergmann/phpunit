<?php
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

class AttributeTest extends ConstraintTestCase
{
    public function testAttributeEqualTo()
    {
        $object     = new \ClassWithNonPublicAttributes;

        $constraint = new Attribute(
            new IsEqual(1),
            'foo'
        );

        $this->assertTrue($constraint->evaluate($object, '', true));
        $this->assertEquals('attribute "foo" is equal to 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        $constraint = new Attribute(
            new IsEqual(2),
            'foo'
        );

        $this->assertFalse($constraint->evaluate($object, '', true));

        try {
            $constraint->evaluate($object);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that attribute "foo" is equal to 2.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testAttributeEqualTo2()
    {
        $object     = new \ClassWithNonPublicAttributes;
        $constraint = new Attribute(
            new IsEqual(2),
            'foo'
        );

        try {
            $constraint->evaluate($object, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that attribute "foo" is equal to 2.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
