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

#[CoversClass(IsEqualWithDelta::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsEqualWithDeltaTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                'is equal to 1.0 with delta <0.000000>',
                '',
                '',
                1.0,
                0.0,
                1.0,
            ],

            [
                true,
                'is equal to 1.0 with delta <0.100000>',
                '',
                '',
                1.0,
                0.1,
                1.09,
            ],

            [
                true,
                <<<'EOT'
is equal to Array &%d [
    0 => 1.0,
] with delta <0.000000>
EOT,
                '',
                '',
                [1.0],
                0.0,
                [1.0],
            ],

            [
                true,
                <<<'EOT'
is equal to Array &%d [
    0 => 1.0,
] with delta <0.100000>
EOT,
                '',
                '',
                [1.0],
                0.1,
                [1.09],
            ],

            [
                true,
                <<<'EOT'
is equal to stdClass Object #%d (
    'property' => 1.0,
) with delta <0.000000>
EOT,
                '',
                '',
                self::stdClass('property', 1.0),
                0.0,
                self::stdClass('property', 1.0),
            ],

            [
                true,
                <<<'EOT'
is equal to stdClass Object #%d (
    'property' => 1.0,
) with delta <0.100000>
EOT,
                '',
                '',
                self::stdClass('property', 1.0),
                0.1,
                self::stdClass('property', 1.09),
            ],

            [
                false,
                'is equal to 1.0 with delta <0.100000>',
                'Failed asserting that 1.1 matches expected 1.0.',
                'Failed asserting that 1.1 matches expected 1.0.',
                1.0,
                0.1,
                1.1,
            ],

            [
                false,
                <<<'EOT'
is equal to Array &%d [
    0 => 1.0,
] with delta <0.000000>
EOT,
                'Failed asserting that two arrays are equal.',
                <<<'EOT'
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => 1.0
+    0 => 1.01
 )

EOT,
                [1.0],
                0.0,
                [1.01],
            ],

            [
                false,
                <<<'EOT'
is equal to stdClass Object #%d (
    'property' => 1.0,
) with delta <0.000000>
EOT,
                'Failed asserting that two objects are equal.',
                <<<'EOT'
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object (
-    'property' => 1.0
+    'property' => 1.01
 )

EOT,
                self::stdClass('property', 1.0),
                0.0,
                self::stdClass('property', 1.01),
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $constraintAsString, string $failureDescription, string $comparisonFailureAsString, mixed $expected, float $delta, mixed $actual): void
    {
        $constraint = new IsEqualWithDelta($expected, $delta);

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

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
    public function testCanBeRepresentedAsString(bool $result, string $constraintAsString, string $failureDescription, string $comparisonFailureAsString, mixed $expected, float $delta, mixed $actual): void
    {
        $constraint = new IsEqualWithDelta($expected, $delta);

        $this->assertStringMatchesFormat($constraintAsString, $constraint->toString(true));
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsEqualWithDelta(1.0, 0.0)));
    }

    private static function stdClass(string $key, float $value): stdClass
    {
        $o = new stdClass;

        $o->{$key} = $value;

        return $o;
    }
}
