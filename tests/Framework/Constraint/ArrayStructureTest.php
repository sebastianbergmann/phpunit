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

class ArrayStructureTest extends ConstraintTestCase
{
    public function testConstraintArrayStructureWithKeysMissingFromActual(): void
    {
        $constraint = new ArrayStructure([
            'a' => ['b', 'c'],
            'd',
            'e',
        ], false);

        $actual = [];
        $this->assertFalse($constraint->evaluate($actual, '', true));
        $this->assertEquals('matches the given structure.', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($actual);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array matches the given structure..
a not available.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayStructureWithActualHavingExtraElementsWhenStrictIsTrue(): void
    {
        $constraint = new ArrayStructure([
            'a',
            'b',
            'c' => ['d', 'e'],
        ], true);
        $actual     = [
            'a' => 'aval',
            'b' => 'bval',
            'c' => [
                'd' => 'dval',
                'e' => ['f' => 'fval'],
            ],
            'f' => 'fval'
        ];
        $this->assertFalse($constraint->evaluate($actual, '', true));
        $this->assertEquals('matches the given structure.', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($actual);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array matches the given structure..
Array has more elements than present in the given structure.
Strict mode is ENABLED.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayStructureWhenAnElementShouldBeArrayButIsNot(): void
    {
        $constraint = new ArrayStructure([
            'a' => ['b', 'c'],
            'd',
            'e',
        ], false);

        $actual = [
            'a' => 'aval',
            'd' => 'dval',
            'e' => 'eval',
        ];
        $this->assertFalse($constraint->evaluate($actual, '', true));
        $this->assertEquals('matches the given structure.', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($actual);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array matches the given structure..
a is not an array.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
