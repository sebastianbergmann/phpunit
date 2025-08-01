<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint\Dictionary;

use AssertionError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(IsIdenticalKeysValues::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsIdenticalKeysValuesTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'expected is not an array' => [
                false,
                'is identical to \'not-array\'',
                '\'string\' is not an instance of \'array\'',
                <<<'EOT'
'string' is not an instance of 'array'
--- Expected
+++ Actual
@@ @@
-'array'
+'string'
EOT
,
                'not-array',
                [],
            ],
            'actual is not an array' => [
                false,
                'is identical to Array &%d []',
                '\'string\' is not an instance of \'array\'',
                <<<'EOT'
'string' is not an instance of 'array'
--- Expected
+++ Actual
@@ @@
-'array'
+'string'
EOT
                ,
                [],
                'not-array',
            ],
            'expected key missing from actual' => [
                false,
                <<<'EOT'
is identical to Array &%d [
    'a' => 0,
]
EOT
                ,
                'Failed asserting that two arrays are equal.',
                <<<'EOT'
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    'a' => 0
+    0 => 0
 )

EOT
                ,
                ['a' => 0],
                [0],
            ],
            'actual has unexpected key' => [
                false,
                <<<'EOT'
is identical to Array &%d [
    0 => 0,
]
EOT
                ,
                'Failed asserting that two arrays are equal.',
                <<<'EOT'
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => 0
+    'a' => 0
 )

EOT
                ,
                [0],
                ['a' => 0],
            ],
            'expected value is array and actual value is not' => [
                false,
                <<<'EOT'
is identical to Array &%d [
    'a' => Array &%d [],
]
EOT
                ,
                'Failed asserting that two arrays are equal.',
                <<<'EOT'
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    'a' => Array &%d []
+    'a' => 0
 )

EOT
                ,
                ['a' => []],
                ['a' => 0],
            ],
            'expected value is object and actual value is not' => [
                false,
                <<<'EOT'
is identical to Array &%d [
    'a' => stdClass Object #%d (),
]
EOT
                ,
                'Failed asserting that two arrays are equal.',
                <<<'EOT'
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    'a' => stdClass Object #%d ()
+    'a' => 0
 )

EOT
                ,
                ['a' => new stdClass],
                ['a' => 0],
            ],
            'expected object value does not match actual object value' => [
                false,
                <<<'EOT'
is identical to Array &%d [
    'a' => stdClass Object #%d (
        'a' => 1,
    ),
]
EOT
                ,
                'Failed asserting that two arrays are equal.',
                <<<'EOT'
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
     'a' => stdClass Object (
-        'a' => 1
+        'a' => '1'
     )
 )

EOT
                ,
                ['a' => self::stdClass('a', 1)],
                ['a' => self::stdClass('a', '1')],
            ],
            'empty arrays are equal' => [
                true,
                'is identical to Array &%d []',
                '',
                '',
                [],
                [],
            ],
            'key equality (string, bool, int)' => [
                true,
                <<<'EOT'
is identical to Array &%d [
    'string' => 'string',
    1 => 1,
]
EOT
                ,
                '',
                '',
                [
                    'string' => 'string',
                    true     => true,
                    1        => 1,
                ],
                [
                    'string' => 'string',
                    true     => true,
                    1        => 1,
                ],
            ],
            'value equality (string, bool, int, float, object, array, dictionary)' => [
                true,
                <<<'EOT'
is identical to Array &%d [
    'string' => 'string',
    1 => 1,
    2 => 2.5,
    'object' => stdClass Object #%d (
        'key' => 'value',
    ),
    'array' => Array &%d [
        0 => 1,
        1 => 2,
        2 => 3,
    ],
    'dictionary' => Array &%d [
        'string' => 'string',
        1 => 1,
        2 => 2.5,
        'object' => stdClass Object #%d (
            'key' => 'value',
        ),
        'array' => Array &%d [
            0 => 1,
            1 => 2,
            2 => 3,
        ],
    ],
]
EOT
                ,
                '',
                '',
                [
                    'string'     => 'string',
                    true         => true,
                    1            => 1,
                    2            => 2.5,
                    'object'     => self::stdClass('key', 'value'),
                    'array'      => [1, 2, 3],
                    'dictionary' => [
                        'string' => 'string',
                        true     => true,
                        1        => 1,
                        2        => 2.5,
                        'object' => self::stdClass('key', 'value'),
                        'array'  => [1, 2, 3],
                    ],
                ],
                [
                    'string'     => 'string',
                    true         => true,
                    1            => 1,
                    2            => 2.5,
                    'object'     => self::stdClass('key', 'value'),
                    'array'      => [1, 2, 3],
                    'dictionary' => [
                        'string' => 'string',
                        true     => true,
                        1        => 1,
                        2        => 2.5,
                        'object' => self::stdClass('key', 'value'),
                        'array'  => [1, 2, 3],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(
        bool $result,
        string $constraintAsString,
        string $failureDescription,
        string $comparisonFailureAsString,
        mixed $expected,
        mixed $actual
    ): void {
        $constraint = new IsIdenticalKeysValues($expected);

        try {
            $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

            if ($result) {
                return;
            }
            $constraint->evaluate($actual);
        } catch (AssertionError $e) {
            $this->assertSame($failureDescription, $e->getMessage());

            return;
        } catch (ExpectationFailedException $e) {
            $this->assertSame($failureDescription, $e->getMessage());
            $this->assertStringMatchesFormat(
                $comparisonFailureAsString,
                $e->getComparisonFailure() ? $e->getComparisonFailure()->toString() : '',
            );

            return;
        }

        $this->fail();
    }

    #[DataProvider('provider')]
    public function testCanBeRepresentedAsString(
        bool $result,
        string $constraintAsString,
        string $failureDescription,
        string $comparisonFailureAsString,
        mixed $expected,
        mixed $actual
    ): void {
        $constraint = new IsIdenticalKeysValues($expected);

        $this->assertStringMatchesFormat($constraintAsString, $constraint->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsIdenticalKeysValues([])));
    }

    private static function stdClass(string $key, mixed $value): stdClass
    {
        $o = new stdClass;

        $o->{$key} = $value;

        return $o;
    }
}
