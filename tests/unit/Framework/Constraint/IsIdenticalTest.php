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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(IsIdentical::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsIdenticalTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                'is identical to 0',
                'Failed asserting that 1 is identical to 0.',
                '',
                0,
                1,
            ],

            [
                'is identical to an object of class "stdClass"',
                'Failed asserting that two variables reference the same object.',
                '',
                new stdClass,
                new stdClass,
            ],

            [
                'is identical to \'expected\'',
                'Failed asserting that two strings are identical.',
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
-'expected'
+'actual'

EOT,
                'expected',
                'actual',
            ],

            [
                <<<'EOT'
is identical to Array &0 [
    0 => 1,
    1 => 2,
    2 => 3,
    3 => 4,
    4 => 5,
    5 => 6,
]
EOT,
                'Failed asserting that two arrays are identical.',
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     0 => 1,
     1 => 2,
-    2 => 3,
+    2 => 33,
     3 => 4,
     4 => 5,
     5 => 6,
 ]

EOT,
                [1, 2, 3, 4, 5, 6],
                [1, 2, 33, 4, 5, 6],
            ],

            [
                <<<'EOT'
is identical to Array &0 [
    0 => Array &1 [
        'A' => 'B',
    ],
    1 => Array &2 [
        'C' => Array &3 [
            0 => 'D',
            1 => 'E',
        ],
    ],
]
EOT,
                'Failed asserting that two arrays are identical.',
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
 Array &0 [
     0 => Array &1 [
-        'A' => 'B',
+        'A' => 'C',
     ],
     1 => Array &2 [
         'C' => Array &3 [
-            0 => 'D',
+            0 => 'C',
             1 => 'E',
+            2 => 'F',
         ],
     ],
 ]

EOT,
                [
                    ['A' => 'B'],
                    [
                        'C' => [
                            'D',
                            'E',
                        ],
                    ],
                ],
                [
                    ['A' => 'C'],
                    [
                        'C' => [
                            'C',
                            'E',
                            'F',
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(string $constraintAsString, string $failureDescription, string $comparisonFailureAsString, mixed $expected, mixed $actual): void
    {
        $constraint = new IsIdentical($expected);

        $this->assertTrue($constraint->evaluate($expected, returnResult: true));
        $this->assertFalse($constraint->evaluate($actual, returnResult: true));

        try {
            $constraint->evaluate($actual);
        } catch (ExpectationFailedException $e) {
            $this->assertSame($failureDescription, $e->getMessage());
            $this->assertSame($comparisonFailureAsString, $e->getComparisonFailure() ? $e->getComparisonFailure()->toString() : '');

            return;
        }

        $this->fail();
    }

    #[DataProvider('provider')]
    public function testCanBeRepresentedAsString(string $constraintAsString, string $failureDescription, string $comparisonFailureAsString, mixed $expected, mixed $actual): void
    {
        $constraint = new IsIdentical($expected);

        $this->assertSame($constraintAsString, $constraint->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsIdentical(true)));
    }
}
