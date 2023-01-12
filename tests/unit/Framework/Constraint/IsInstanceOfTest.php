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
use ArrayObject;
use Countable;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\ThrowableToStringMapper;
use ReflectionException;
use stdClass;

#[CoversClass(IsInstanceOf::class)]
#[Small]
final class IsInstanceOfTest extends ConstraintTestCase
{
    public function testConstraintInstanceOf(): void
    {
        $constraint = new IsInstanceOf(stdClass::class);

        $this->assertTrue($constraint->evaluate(new stdClass, '', true));
    }

    public function testConstraintFailsOnString(): void
    {
        $constraint = new IsInstanceOf(stdClass::class);

        try {
            $constraint->evaluate(stdClass::class);
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                sprintf(
                    <<<'EOT'
Failed asserting that '%s' is an instance of class "%s".

EOT
                    ,
                    stdClass::class,
                    stdClass::class
                ),
                ThrowableToStringMapper::map($e)
            );
        }
    }

    public function testCronstraintsThrowsReflectionException(): void
    {
        $this->throwException(new ReflectionException);

        $constraint = new IsInstanceOf(NotExistingClass::class);

        $this->assertSame(
            sprintf(
                'is instance of class "%s"',
                NotExistingClass::class
            ),
            $constraint->toString()
        );
    }

    public function testConstraintIsInstanceOf(): void
    {
        $constraint = Assert::isInstanceOf(\Exception::class);

        $this->assertFalse($constraint->evaluate(new stdClass, '', true));
        $this->assertTrue($constraint->evaluate(new \Exception, '', true));
        $this->assertEquals(
            sprintf(
                'is instance of class "%s"',
                \Exception::class
            ),
            $constraint->toString()
        );
        $this->assertCount(1, $constraint);

        $interfaceConstraint = Assert::isInstanceOf(Countable::class);
        $this->assertFalse($interfaceConstraint->evaluate(new stdClass, '', true));
        $this->assertTrue($interfaceConstraint->evaluate(new ArrayObject, '', true));
        $this->assertEquals(
            sprintf(
                'is instance of interface "%s"',
                Countable::class
            ),
            $interfaceConstraint->toString()
        );

        try {
            $constraint->evaluate(new stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                sprintf(
                    <<<'EOF'
Failed asserting that %s Object () is an instance of class "%s".

EOF
                    ,
                    stdClass::class,
                    \Exception::class
                ),
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsInstanceOf2(): void
    {
        $constraint = Assert::isInstanceOf(\Exception::class);

        try {
            $constraint->evaluate(new stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                sprintf(
                    <<<'EOF'
custom message
Failed asserting that %s Object () is an instance of class "%s".

EOF
                    ,
                    stdClass::class,
                    \Exception::class
                ),
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotInstanceOf(): void
    {
        $constraint = Assert::logicalNot(
            Assert::isInstanceOf(stdClass::class)
        );

        $this->assertFalse($constraint->evaluate(new stdClass, '', true));
        $this->assertTrue($constraint->evaluate(new Exception, '', true));
        $this->assertEquals(
            sprintf(
                'is not instance of class "%s"',
                stdClass::class
            ),
            $constraint->toString()
        );
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(new stdClass);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                sprintf(
                    <<<'EOF'
Failed asserting that %s Object () is not an instance of class "%s".

EOF
                    ,
                    stdClass::class,
                    stdClass::class
                ),
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotInstanceOf2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::isInstanceOf(stdClass::class)
        );

        try {
            $constraint->evaluate(new stdClass, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                sprintf(
                    <<<'EOF'
custom message
Failed asserting that %s Object () is not an instance of class "%s".

EOF
                    ,
                    stdClass::class,
                    stdClass::class
                ),
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }
}
