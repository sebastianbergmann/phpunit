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
use ReflectionException;
use stdClass;

/**
 * @small
 */
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
                TestFailure::exceptionToString($e)
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
}
