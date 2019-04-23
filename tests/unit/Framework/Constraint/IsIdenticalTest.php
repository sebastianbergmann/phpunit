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
final class IsIdenticalTest extends ConstraintTestCase
{
    public function testConstraintIsIdentical(): void
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $constraint = new IsIdentical($a);

        $this->assertFalse($constraint->evaluate($b, '', true));
        $this->assertTrue($constraint->evaluate($a, '', true));
        $this->assertEquals('is identical to an object of class "stdClass"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($b);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that two variables reference the same object.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsIdentical2(): void
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $constraint = new IsIdentical($a);

        try {
            $constraint->evaluate($b, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that two variables reference the same object.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsIdentical3(): void
    {
        $constraint = new IsIdentical('a');

        try {
            $constraint->evaluate('b', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'a'
+'b'

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsIdenticalArrayDiff(): void
    {
        $expected = [1, 2, 3, 4, 5, 6];
        $actual   = [1, 2, 33, 4, 5, 6];

        $constraint = new IsIdentical($expected);

        try {
            $constraint->evaluate($actual, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                <<<EOF
custom message
Failed asserting that two arrays are identical.
--- Expected
+++ Actual
@@ @@
 Array &0 (
     0 => 1
     1 => 2
-    2 => 3
+    2 => 33
     3 => 4
     4 => 5
     5 => 6
 )

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsIdenticalNestedArrayDiff(): void
    {
        $expected = [
            ['A' => 'B'],
            [
                'C' => [
                    'D',
                    'E',
                ],
            ],
        ];
        $actual = [
            ['A' => 'C'],
            [
                'C' => [
                    'C',
                    'E',
                    'F',
                ],
            ],
        ];
        $constraint = new IsIdentical($expected);

        try {
            $constraint->evaluate($actual, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that two arrays are identical.
--- Expected
+++ Actual
@@ @@
 Array &0 (
     0 => Array &1 (
-        'A' => 'B'
+        'A' => 'C'
     )
     1 => Array &2 (
         'C' => Array &3 (
-            0 => 'D'
+            0 => 'C'
             1 => 'E'
+            2 => 'F'
         )
     )
 )

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
