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
use PHPUnit\TestFixture\ObjectComparison\ChildOfValueObjectA;
use PHPUnit\TestFixture\ObjectComparison\SameStructAsValueObjectA;
use PHPUnit\TestFixture\ObjectComparison\ValueObjectA;
use stdClass;

#[CoversMethod(Assert::class, 'assertArraysAreEqual')]
#[TestDox('assertArraysAreEqual()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertArraysAreEqualTest extends TestCase
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

            'integer vs float (loose comparison)' => [
                [1],
                [1.0],
            ],

            'true vs 1 (loose comparison)' => [
                [true],
                [1],
            ],

            'false vs 0 (loose comparison)' => [
                [false],
                [0],
            ],

            'different object instances (loose comparison)' => [
                ['obj' => new stdClass],
                ['obj' => new stdClass],
            ],

            'different object instances with strictly equal properties' => [
                ['obj' => new ValueObjectA('value')],
                ['obj' => new ValueObjectA('value')],
            ],

            'different object instances with loosely equal properties' => [
                ['obj' => new ValueObjectA(false)],
                ['obj' => new ValueObjectA(0)],
            ],

            'null vs empty string (loose comparison)' => [
                ['a' => null],
                ['a' => ''],
            ],

            'false vs null (loose comparison)' => [
                [false],
                [null],
            ],

            'identical keys, identical values, different order (loose comparison)' => [
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
-    0 => 1,
+    0 => 2,
     'a' => 'b',
     2 => 'c',
 ]

EOT,
                [0 => 1, 'a' => 'b', 2 => 'c'],
                [0 => 2, 'a' => 'b', 2 => 'c'],
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

            'different object instances with strictly equal properties, different order' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => PHPUnit\TestFixture\ObjectComparison\ValueObjectA Object #%d (
+    0 => PHPUnit\TestFixture\ObjectComparison\ValueObjectA Object #%d (
+        'value' => 'v2',
+    ),
+    1 => PHPUnit\TestFixture\ObjectComparison\ValueObjectA Object #%d (
         'value' => 'v1',
-    ),
-    1 => PHPUnit\TestFixture\ObjectComparison\ValueObjectA Object #%d (
-        'value' => 'v2',
     ),
 ]

EOT,
                [new ValueObjectA('v1'), new ValueObjectA('v2')],
                [new ValueObjectA('v2'), new ValueObjectA('v1')],
            ],

            'object instances with different values' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => stdClass Object #%d (
-        'a' => 'a',
+    0 => stdClass Object #%d (
+        'a' => 'A',
     ),
 ]

EOT,
                [(object) ['a' => 'a']],
                [(object) ['a' => 'A']],
            ],

            'object instances with the same structure but of different classes' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => PHPUnit\TestFixture\ObjectComparison\ValueObjectA Object #%d (
+    0 => PHPUnit\TestFixture\ObjectComparison\SameStructAsValueObjectA Object #%d (
         'value' => 'a',
     ),
 ]

EOT,
                [new ValueObjectA('a')],
                [new SameStructAsValueObjectA('a')],
            ],

            'object instances with the same structure but of different classes but with a common root' => [
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => PHPUnit\TestFixture\ObjectComparison\ValueObjectA Object #%d (
+    0 => PHPUnit\TestFixture\ObjectComparison\ChildOfValueObjectA Object #%d (
         'value' => 'a',
     ),
 ]

EOT,
                [new ValueObjectA('a')],
                [new ChildOfValueObjectA('a')],
            ],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(array $expected, array $actual): void
    {
        $this->assertArraysAreEqual($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $comparisonFailure, array $expected, array $actual): void
    {
        try {
            $this->assertArraysAreEqual($expected, $actual);
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Failed asserting that two arrays are equal.', $e->getMessage());
            $this->assertStringMatchesFormat($comparisonFailure, $e->getComparisonFailure()->toString());

            return;
        }

        $this->fail();
    }
}
