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

/**
 * @small
 */
final class IsInstanceOfTest extends ConstraintTestCase
{
    public function testConstraintInstanceOf(): void
    {
        $constraint = new IsInstanceOf(\stdClass::class);

        self::assertTrue($constraint->evaluate(new \stdClass, '', true));
    }

    public function testConstraintFailsOnString(): void
    {
        $constraint = new IsInstanceOf(\stdClass::class);

        try {
            $constraint->evaluate('stdClass');
        } catch (ExpectationFailedException $e) {
            self::assertSame(
                <<<EOT
Failed asserting that 'stdClass' is an instance of class "stdClass".

EOT
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }

    public function testCronstraintsThrowsReflectionException(): void
    {
        $this->throwException(new \ReflectionException);

        $constraint = new IsInstanceOf(NotExistingClass::class);

        self::assertSame(
            'is instance of class "PHPUnit\Framework\Constraint\NotExistingClass"',
            $constraint->toString()
        );
    }
}
