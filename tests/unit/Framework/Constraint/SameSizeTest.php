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

class SameSizeTest extends ConstraintTestCase
{
    public function testConstraintSameSizeWithAnArray(): void
    {
        $constraint = new SameSize([1, 2, 3, 4, 5]);

        $this->assertTrue($constraint->evaluate([6, 7, 8, 9, 10], '', true));
        $this->assertFalse($constraint->evaluate([1, 2, 3, 4], '', true));
    }

    public function testConstraintSameSizeWithAnIteratorWhichDoesNotImplementCountable(): void
    {
        $constraint = new SameSize(new \TestIterator([1, 2, 3, 4, 5]));

        $this->assertTrue($constraint->evaluate(new \TestIterator([6, 7, 8, 9, 10]), '', true));
        $this->assertFalse($constraint->evaluate(new \TestIterator([1, 2, 3, 4]), '', true));
    }

    public function testConstraintSameSizeWithAnObjectImplementingCountable(): void
    {
        $constraint = new SameSize(new \ArrayObject([1, 2, 3, 4, 5]));

        $this->assertTrue($constraint->evaluate(new \ArrayObject([6, 7, 8, 9, 10]), '', true));
        $this->assertFalse($constraint->evaluate(new \ArrayObject([1, 2, 3, 4]), '', true));
    }

    public function testConstraintSameSizeFailing(): void
    {
        $constraint = new SameSize([1, 2, 3, 4, 5]);

        try {
            $constraint->evaluate([1, 2]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that actual size 2 matches expected size 5.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
