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

#[CoversClass(IsEqualCanonicalizing::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsEqualCanonicalizingTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                <<<'EOT'
is equal to Array &%d (
    0 => 'value'
)
EOT,
                '',
                '',
                ['value'],
                ['value'],
            ],

            [
                true,
                <<<'EOT'
is equal to Array &%d (
    0 => 'value'
    1 => 'another-value'
)
EOT,
                '',
                '',
                ['value', 'another-value'],
                ['another-value', 'value'],
            ],

            [
                true,
                <<<'EOT'
is equal to stdClass Object #%d (
    'foo' => 'bar'
)
EOT,
                '',
                '',
                self::stdClass('foo', 'bar'),
                self::stdClass('foo', 'bar'),
            ],

            [
                true,
                'is equal to true',
                '',
                '',
                true,
                true,
            ],

            [
                true,
                'is equal to true',
                '',
                '',
                true,
                'true',
            ],

            [
                true,
                'is equal to 1.0',
                '',
                '',
                1.0,
                1.0,
            ],

            [
                true,
                'is equal to 1.0',
                '',
                '',
                1.0,
                1,
            ],

            [
                true,
                'is equal to 1',
                '',
                '',
                1,
                1,
            ],

            [
                true,
                'is equal to 1',
                '',
                '',
                1,
                1.0,
            ],

            [
                true,
                'is equal to 1',
                '',
                '',
                1,
                '1',
            ],

            [
                true,
                'is equal to \'1\'',
                '',
                '',
                '1',
                1,
            ],

            [
                true,
                'is equal to \'string\'',
                '',
                '',
                'string',
                'string',
            ],

            [
                true,
                'is equal to <text>',
                '',
                '',
                'string' . PHP_EOL . 'string',
                'string' . PHP_EOL . 'string',
            ],

            [
                false,
                <<<'EOT'
is equal to Array &%d (
    0 => 'value'
)
EOT,
                'Failed asserting that two arrays are equal.',
                <<<'EOT'
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => 'value'
+    0 => 'another-value'
 )

EOT,
                ['value'],
                ['another-value'],
            ],

            [
                false,
                <<<'EOT'
is equal to stdClass Object #%d (
    'foo' => 'bar'
)
EOT,
                'Failed asserting that two objects are equal.',
                <<<'EOT'
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object (
-    0 => 'bar'
+    0 => 'foo'
 )

EOT,
                self::stdClass('foo', 'bar'),
                self::stdClass('bar', 'foo'),
            ],

            [
                false,
                'is equal to true',
                'Failed asserting that false matches expected true.',
                'Failed asserting that false matches expected true.',
                true,
                false,
            ],

            [
                false,
                'is equal to 1.0',
                'Failed asserting that 1.01 matches expected 1.0.',
                'Failed asserting that 1.01 matches expected 1.0.',
                1.0,
                1.01,
            ],

            [
                false,
                'is equal to 1.01',
                'Failed asserting that 1 matches expected 1.01.',
                'Failed asserting that 1 matches expected 1.01.',
                1.01,
                1,
            ],

            [
                false,
                'is equal to 1',
                'Failed asserting that \'2\' matches expected 1.',
                'Failed asserting that \'2\' matches expected 1.',
                1,
                '2',
            ],

            [
                false,
                'is equal to \'1\'',
                'Failed asserting that 2 matches expected \'1\'.',
                'Failed asserting that 2 matches expected \'1\'.',
                '1',
                2,
            ],

            [
                false,
                'is equal to \'string\'',
                'Failed asserting that two strings are equal.',
                <<<'EOT'
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'string'
+'another-string'

EOT,
                'string',
                'another-string',
            ],

            [
                false,
                'is equal to <text>',
                'Failed asserting that two strings are equal.',
                <<<'EOT'
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'string\n
-string'
+'another-string\n
+another-string'

EOT,
                "string\nstring",
                "another-string\nanother-string",
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $constraintAsString, string $failureDescription, string $comparisonFailureAsString, mixed $expected, mixed $actual): void
    {
        $constraint = new IsEqualCanonicalizing($expected);

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
    public function testCanBeRepresentedAsString(bool $result, string $constraintAsString, string $failureDescription, string $comparisonFailureAsString, mixed $expected, mixed $actual): void
    {
        $constraint = new IsEqualCanonicalizing($expected);

        $this->assertStringMatchesFormat($constraintAsString, $constraint->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsEqualCanonicalizing(true)));
    }

    private static function stdClass(string $key, string $value): stdClass
    {
        $o = new stdClass;

        $o->{$key} = $value;

        return $o;
    }
}
