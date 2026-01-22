<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;

#[CoversMethod(Assert::class, 'assertArraysHaveIdenticalValues')]
#[TestDox('assertArraysHaveIdenticalValues()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertArraysHaveIdenticalValuesTest extends TestCase
{
    /**
     * @return non-empty-array<non-empty-string, array{0: array<mixed>, 1: array<mixed>}>
     */
    public static function successProvider(): array
    {
        $object = new stdClass;

        return [
            'empty arrays' => [
                [],
                [],
            ],

            'implicit integer keys' => [
                ['a', 'b', 'c'],
                ['a', 'b', 'c'],
            ],

            'explicit integer keys' => [
                [1 => 'a', 2 => 'b', 4 => 'c'],
                [1 => 'a', 2 => 'b', 4 => 'c'],
            ],

            'explicit negative integer keys' => [
                [-1 => 'a', -2 => 'b'],
                [-1 => 'a', -2 => 'b'],
            ],

            'explicit numeric keys (literal strings, auto-cast to int)' => [
                ['1' => 'a', '2' => 'b', '4' => 'c'],
                ['1' => 'a', '2' => 'b', '4' => 'c'],
            ],

            'different explicit numeric keys (literal strings, auto-cast to int)' => [
                [1 => 'a', 2 => 'b', 4 => 'c'],
                ['1' => 'a', '2' => 'b', '4' => 'c'],
            ],

            'string keys' => [
                ['a' => 1, 'b' => 2, 'c' => 3],
                ['a' => 1, 'b' => 2, 'c' => 3],
            ],

            'nested arrays' => [
                ['a' => ['b' => ['c' => 1]]],
                ['a' => ['b' => ['c' => 1]]],
            ],

            'more deeply nested arrays' => [
                [['a' => [['b' => 'c']]]],
                [['a' => [['b' => 'c']]]],
            ],

            'mixed value types' => [
                [1, 'string', 3.14, true, null],
                [1, 'string', 3.14, true, null],
            ],

            'object references' => [
                ['obj' => $object],
                ['obj' => $object],
            ],

            'null values' => [
                ['a' => null, 'b' => null],
                ['a' => null, 'b' => null],
            ],

            'boolean values' => [
                [true, false, true],
                [true, false, true],
            ],

            'float values' => [
                [1.1, 2.2, 3.3],
                [1.1, 2.2, 3.3],
            ],

            'different keys, identical values in same order' => [
                ['a' => 1, 'b' => 2],
                ['x' => 1, 'y' => 2],
            ],

            'integer keys vs string keys, identical values in same order' => [
                [0 => 'a', 1 => 'b'],
                ['x' => 'a', 'y' => 'b'],
            ],
        ];
    }

    /**
     * @return non-empty-array<non-empty-string, array{0: non-empty-string, 1: array<mixed>, 2: array<mixed>}>
     */
    public static function failureProvider(): array
    {
        return [
            'empty expected' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
-Array &0 []
+Array &0 [
+    0 => 1,
+]

EOT,
                [],
                [1],
            ],

            'empty actual' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
-Array &0 [
-    0 => 1,
-]
+Array &0 []

EOT,
                [1],
                [],
            ],

            'extra element in expected' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     0 => 1,
     1 => 2,
-    2 => 3,
 ]

EOT,
                [1, 2, 3],
                [1, 2],
            ],

            'extra element in actual' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     0 => 1,
     1 => 2,
+    2 => 3,
 ]

EOT,
                [1, 2],
                [1, 2, 3],
            ],

            'different values in same order' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     0 => 1,
-    1 => 2,
+    1 => 3,
 ]

EOT,
                [1, 2],
                [1, 3],
            ],

            'identical values, different order' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => 1,
+    0 => 3,
     1 => 2,
-    2 => 3,
+    2 => 1,
 ]

EOT,
                [1, 2, 3],
                [3, 2, 1],
            ],

            'different nested values' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     0 => Array &1 [
-        'b' => 1,
+        'b' => 2,
     ],
 ]

EOT,
                [['b' => 1]],
                [['b' => 2]],
            ],

            'different object instances' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => stdClass Object #%d (),
+    0 => stdClass Object #%d (),
 ]

EOT,
                [new stdClass],
                [new stdClass],
            ],

            'null vs empty string' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => null,
+    0 => '',
 ]

EOT,
                [null],
                [''],
            ],

            'integer vs float' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => 1,
+    0 => 1.0,
 ]

EOT,
                [1],
                [1.0],
            ],

            'true vs 1' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => true,
+    0 => 1,
 ]

EOT,
                [true],
                [1],
            ],

            'false vs 0' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => false,
+    0 => 0,
 ]

EOT,
                [false],
                [0],
            ],

            'false vs null' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => false,
+    0 => null,
 ]

EOT,
                [false],
                [null],
            ],

            'different nested array order' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     0 => Array &1 [
-        0 => 1,
+        0 => 3,
         1 => 2,
-        2 => 3,
+        2 => 1,
     ],
 ]

EOT,
                [[1, 2, 3]],
                [[3, 2, 1]],
            ],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(array $expected, array $actual): void
    {
        $this->assertArraysHaveIdenticalValues($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $comparisonFailure, array $expected, array $actual): void
    {
        try {
            $this->assertArraysHaveIdenticalValues($expected, $actual);
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Failed asserting that two arrays are identical while ignoring keys.', $e->getMessage());
            $this->assertStringMatchesFormat($comparisonFailure, $e->getComparisonFailure()->toString());

            return;
        }

        $this->fail();
    }
}
