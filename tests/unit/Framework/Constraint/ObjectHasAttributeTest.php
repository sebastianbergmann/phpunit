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

use function sprintf;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
use PHPUnit\TestFixture\ClassWithNonPublicAttributes;
use stdClass;

/**
 * @small
 */
final class ObjectHasAttributeTest extends ConstraintTestCase
{
    public function testConstraintObjectHasAttribute(): void
    {
        $constraint = new ObjectHasAttribute('privateAttribute');

        $this->assertTrue($constraint->evaluate(new ClassWithNonPublicAttributes, '', true));
        $this->assertFalse($constraint->evaluate(new stdClass, '', true));
        $this->assertEquals('has attribute "privateAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(new stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                sprintf(
                    <<<'EOF'
Failed asserting that object of class "%s" has attribute "privateAttribute".

EOF
                    ,
                    stdClass::class,
                ),
                TestFailure::exceptionToString($e),
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintObjectHasAttribute2(): void
    {
        $constraint = new ObjectHasAttribute('privateAttribute');

        try {
            $constraint->evaluate(new stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                sprintf(
                    <<<'EOF'
custom message
Failed asserting that object of class "%s" has attribute "privateAttribute".

EOF
                    ,
                    stdClass::class,
                ),
                TestFailure::exceptionToString($e),
            );

            return;
        }

        $this->fail();
    }
}
