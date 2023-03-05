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

use function json_encode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonMatches::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class JsonMatchesTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'valid JSON' => [
                true,
                '',
                '',
                json_encode(['Mascott' => 'Tux']),
                json_encode(['Mascott' => 'Tux']),
            ],

            'object fields are unordered' => [
                true,
                '',
                '',
                '{"second":"2", "first":1}',
                '{"first":1, "second":"2"}',
            ],

            'child object fields are unordered' => [
                true,
                '',
                '',
                '{"Mascott": {"age":5, "name":"Tux"}}',
                '{"Mascott": {"name":"Tux", "age":5}}',
            ],

            'single boolean valid json' => [
                true,
                '',
                '',
                'true',
                'true',
            ],

            'single number valid json' => [
                true,
                '',
                '',
                '5.3',
                '5.3',
            ],

            'single null valid json' => [
                true,
                '',
                '',
                'null',
                'null',
            ],

            'invalid JSON in class instantiation' => [
                false,
                'Failed asserting that \'{"Mascott":"Tux"}\' matches JSON string "{"Mascott"::}".',
                '',
                '{"Mascott"::}',
                json_encode(['Mascott' => 'Tux']),
            ],

            'error syntax' => [
                false,
                'Failed asserting that \'{"Mascott"::}\' matches JSON string "{"Mascott":"Tux"}".',
                '',
                json_encode(['Mascott' => 'Tux']),
                '{"Mascott"::}',
            ],

            'error UTF-8' => [
                false,
                'Failed asserting that \'' . json_encode('\xB1\x31') . '\' matches JSON string "{"Mascott":"Tux"}".',
                <<<'EOT'
Failed asserting that two json values are equal.
--- Expected
+++ Actual
@@ @@
-{
-    "Mascott": "Tux"
-}
+"\\xB1\\x31"

EOT,
                json_encode(['Mascott' => 'Tux']),
                json_encode('\xB1\x31'),
            ],

            'string type not equals number' => [
                false,
                'Failed asserting that \'{"age": "5"}\' matches JSON string "{"age": 5}".',
                <<<'EOT'
Failed asserting that two json values are equal.
--- Expected
+++ Actual
@@ @@
 {
-    "age": 5
+    "age": "5"
 }

EOT,
                '{"age": 5}',
                '{"age": "5"}',
            ],

            'string type not equals boolean' => [
                false,
                'Failed asserting that \'{"age": "true"}\' matches JSON string "{"age": true}".',
                <<<'EOT'
Failed asserting that two json values are equal.
--- Expected
+++ Actual
@@ @@
 {
-    "age": true
+    "age": "true"
 }

EOT,
                '{"age": true}',
                '{"age": "true"}',
            ],

            'string type not equals null' => [
                false,
                'Failed asserting that \'{"age": "null"}\' matches JSON string "{"age": null}".',
                <<<'EOT'
Failed asserting that two json values are equal.
--- Expected
+++ Actual
@@ @@
 {
-    "age": null
+    "age": "null"
 }

EOT,
                '{"age": null}',
                '{"age": "null"}',
            ],

            'null field different from missing field' => [
                false,
                'Failed asserting that \'{"present": true, "missing": null}\' matches JSON string "{"present": true}".',
                <<<'EOT'
Failed asserting that two json values are equal.
--- Expected
+++ Actual
@@ @@
 {
+    "missing": null,
     "present": true
 }

EOT,
                '{"present": true}',
                '{"present": true, "missing": null}',
            ],

            'array elements are ordered' => [
                false,
                'Failed asserting that \'["first", "second"]\' matches JSON string "["second", "first"]".',
                <<<'EOT'
Failed asserting that two json values are equal.
--- Expected
+++ Actual
@@ @@
 [
-    "second",
-    "first"
+    "first",
+    "second"
 ]

EOT,
                '["second", "first"]',
                '["first", "second"]',
            ],

            'objects are not arrays' => [
                false,
                'Failed asserting that \'{}\' matches JSON string "[]".',
                <<<'EOT'
Failed asserting that two json values are equal.
--- Expected
+++ Actual
@@ @@
-[]
+{}

EOT,
                '[]',
                '{}',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $comparisonFailureAsString, mixed $expected, mixed $actual): void
    {
        $constraint = new JsonMatches($expected);

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

    public function testCanBeRepresentedAsString(): void
    {
        $constraint = new JsonMatches(json_encode(['key' => 'value']));

        $this->assertSame('matches JSON string "{"key":"value"}"', $constraint->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new JsonMatches(json_encode(['key' => 'value']))));
    }
}
