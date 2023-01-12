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

use function preg_replace;
use function sprintf;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\ThrowableToStringMapper;
use stdClass;

#[CoversClass(IsIdentical::class)]
#[Small]
final class IsIdenticalTest extends ConstraintTestCase
{
    public function testConstraintIsIdentical(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new IsIdentical($a);

        $this->assertFalse($constraint->evaluate($b, '', true));
        $this->assertTrue($constraint->evaluate($a, '', true));
        $this->assertEquals(
            sprintf(
                'is identical to an object of class "%s"',
                stdClass::class
            ),
            $constraint->toString()
        );
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($b);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that two variables reference the same object.

EOF
                ,
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsIdentical2(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = new IsIdentical($a);

        try {
            $constraint->evaluate($b, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
custom message
Failed asserting that two variables reference the same object.

EOF
                ,
                ThrowableToStringMapper::map($e)
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
                <<<'EOF'
custom message
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'a'
+'b'

EOF
                ,
                ThrowableToStringMapper::map($e)
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
                <<<'EOF'
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
                ThrowableToStringMapper::map($e)
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
                <<<'EOF'
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
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical(): void
    {
        $a = new stdClass;
        $b = new stdClass;

        $constraint = Assert::logicalNot(
            Assert::identicalTo($a)
        );

        $this->assertTrue($constraint->evaluate($b, '', true));
        $this->assertFalse($constraint->evaluate($a, '', true));
        $this->assertEquals(
            sprintf(
                'is not identical to an object of class "%s"',
                stdClass::class
            ),
            $constraint->toString()
        );
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate($a);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that two variables don't reference the same object.

EOF
                ,
                $this->trimNewlines(ThrowableToStringMapper::map($e))
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical2(): void
    {
        $a = new stdClass;

        $constraint = Assert::logicalNot(
            Assert::identicalTo($a)
        );

        try {
            $constraint->evaluate($a, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
custom message
Failed asserting that two variables don't reference the same object.

EOF
                ,
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintIsNotIdentical3(): void
    {
        $constraint = Assert::logicalNot(
            Assert::identicalTo('a')
        );

        try {
            $constraint->evaluate('a', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
custom message
Failed asserting that two strings are not identical.

EOF
                ,
                $this->trimNewlines(ThrowableToStringMapper::map($e))
            );

            return;
        }

        $this->fail();
    }

    private function trimNewlines(string $string): string
    {
        return preg_replace('/[ ]*\n/', "\n", $string);
    }
}
