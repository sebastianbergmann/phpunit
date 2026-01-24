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

#[CoversMethod(Assert::class, 'assertArraysAreIdenticalIgnoringOrder')]
#[TestDox('assertArraysAreIdenticalIgnoringOrder()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertArraysAreIdenticalIgnoringOrderTest extends TestCase
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

            'identical keys, identical values, different order' => [
                [0 => 1, 'a' => 'b', 2 => 'c'],
                ['a' => 'b', 0 => 1, 2 => 'c'],
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

            'different keys, identical values' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     'a' => 1,
-    'b' => 2,
+    'c' => 2,
 ]

EOT,
                ['a' => 1, 'b' => 2],
                ['a' => 1, 'c' => 2],
            ],

            'identical keys, different values' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => '1',
+    0 => 1,
     'a' => 'b',
     2 => 'c',
 ]

EOT,
                [0 => '1', 'a' => 'b', 2 => 'c'],
                [0 => 1, 'a' => 'b', 2 => 'c'],
            ],

            'different nested values' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     'a' => Array &1 [
-        'b' => 1,
+        'b' => 2,
     ],
 ]

EOT,
                ['a' => ['b' => 1]],
                ['a' => ['b' => 2]],
            ],

            'different object instances' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    'obj' => stdClass Object #%d (),
+    'obj' => stdClass Object #%d (),
 ]

EOT,
                ['obj' => new stdClass],
                ['obj' => new stdClass],
            ],

            'null vs empty string' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    'a' => null,
+    'a' => '',
 ]

EOT,
                ['a' => null],
                ['a' => ''],
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

            'missing nested key' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     'a' => Array &1 [
         'b' => 1,
-        'c' => 2,
     ],
 ]

EOT,
                ['a' => ['b' => 1, 'c' => 2]],
                ['a' => ['b' => 1]],
            ],

            'different nested array order' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     'a' => Array &1 [
-        0 => 1,
+        0 => 3,
         1 => 2,
-        2 => 3,
+        2 => 1,
     ],
 ]

EOT,
                ['a' => [1, 2, 3]],
                ['a' => [3, 2, 1]],
            ],

            'identical keys in different order, one value differs' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
+    'x' => 1,
+    'y' => 99,
     'z' => 3,
-    'y' => 2,
-    'x' => 1,
 ]

EOT,
                ['z' => 3, 'y' => 2, 'x' => 1],
                ['x' => 1, 'y' => 99, 'z' => 3],
            ],

            'identical integer keys in different order, one value differs' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
+    0 => 'a',
+    1 => 'B',
     2 => 'c',
-    1 => 'b',
-    0 => 'a',
 ]

EOT,
                [2 => 'c', 1 => 'b', 0 => 'a'],
                [0 => 'a', 1 => 'B', 2 => 'c'],
            ],

            'mixed keys in different order, one value differs' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
+    'baz' => 'QUX',
     'foo' => 'bar',
     0 => 1,
-    'baz' => 'qux',
 ]

EOT,
                ['foo' => 'bar', 0 => 1, 'baz' => 'qux'],
                ['baz' => 'QUX', 'foo' => 'bar', 0 => 1],
            ],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(array $expected, array $actual): void
    {
        $this->assertArraysAreIdenticalIgnoringOrder($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $comparisonFailure, array $expected, array $actual): void
    {
        try {
            $this->assertArraysAreIdenticalIgnoringOrder($expected, $actual);
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Failed asserting that two arrays are identical while ignoring order.', $e->getMessage());
            $this->assertStringMatchesFormat($comparisonFailure, $e->getComparisonFailure()->toString());

            return;
        }

        $this->fail();
    }
}
