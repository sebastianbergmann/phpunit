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

use function fclose;
use function fopen;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(IsIdentical::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class IsIdenticalTest extends TestCase
{
    /**
     * @return non-empty-list<array{string, string, string, mixed, mixed}>
     */
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

    public function testFailureDescriptionForResources(): void
    {
        $expected = fopen('php://memory', 'r');
        $actual   = fopen('php://memory', 'r');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that two variables reference the same resource.');

        try {
            new IsIdentical($expected)->evaluate($actual);
        } finally {
            fclose($expected);
            fclose($actual);
        }
    }

    public function testCanBeNegated(): void
    {
        $this->assertSame(
            'is not identical to an object of class "stdClass"',
            new LogicalNot(new IsIdentical(new stdClass))->toString(),
        );

        $this->assertSame(
            'is not identical to 0',
            new LogicalNot(new IsIdentical(0))->toString(),
        );

        $object = new stdClass;

        $this->assertSame(
            'Failed asserting that two variables do not reference the same object.',
            $this->negatedFailureDescription(new IsIdentical($object), $object),
        );

        $this->assertSame(
            'Failed asserting that two strings are not identical.',
            $this->negatedFailureDescription(new IsIdentical('foo'), 'foo'),
        );

        $this->assertSame(
            'Failed asserting that two arrays are not identical.',
            $this->negatedFailureDescription(new IsIdentical([1, 2]), [1, 2]),
        );

        $this->assertSame(
            'Failed asserting that 0 is not identical to 0.',
            $this->negatedFailureDescription(new IsIdentical(0), 0),
        );
    }

    public function testCanBeNegatedForResources(): void
    {
        $resource = fopen('php://memory', 'r');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that two variables do not reference the same resource.');

        try {
            new LogicalNot(new IsIdentical($resource))->evaluate($resource);
        } finally {
            fclose($resource);
        }
    }

    private function negatedFailureDescription(IsIdentical $constraint, mixed $actual): string
    {
        try {
            new LogicalNot($constraint)->evaluate($actual);
        } catch (ExpectationFailedException $e) {
            return $e->getMessage();
        }

        $this->fail();
    }
}
