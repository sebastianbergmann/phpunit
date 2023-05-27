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
final class ClassHasStaticAttributeTest extends ConstraintTestCase
{
    public function testConstraintClassHasStaticAttribute(): void
    {
        $constraint = new ClassHasStaticAttribute('privateStaticAttribute');

        $this->assertTrue($constraint->evaluate(ClassWithNonPublicAttributes::class, '', true));
        $this->assertFalse($constraint->evaluate(stdClass::class, '', true));
        $this->assertEquals('has static attribute "privateStaticAttribute"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(stdClass::class);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                sprintf(
                    <<<'EOF'
Failed asserting that class "%s" has static attribute "privateStaticAttribute".

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

    public function testConstraintClassHasStaticAttribute2(): void
    {
        $constraint = new ClassHasStaticAttribute('foo');

        try {
            $constraint->evaluate(stdClass::class, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                sprintf(
                    <<<'EOF'
custom message
Failed asserting that class "%s" has static attribute "foo".

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
