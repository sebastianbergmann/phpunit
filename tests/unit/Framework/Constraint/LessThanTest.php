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
final class LessThanTest extends ConstraintTestCase
{
    public function testConstraintLessThan(): void
    {
        $constraint = new LessThan(1);

        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertFalse($constraint->evaluate(1, '', true));
        $this->assertEquals('is less than 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(1);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that 1 is less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintLessThan2(): void
    {
        $constraint = new LessThan(1);

        try {
            $constraint->evaluate(1, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
custom message
Failed asserting that 1 is less than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
