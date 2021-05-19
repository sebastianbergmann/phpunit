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
            'valid JSON'                                    => [true, json_encode(['Mascott' => 'Tux']), json_encode(['Mascott' => 'Tux'])],
            'error syntax'                                  => [false, '{"Mascott"::}', json_encode(['Mascott' => 'Tux'])],
            'error UTF-8'                                   => [false, json_encode('\xB1\x31'), json_encode(['Mascott' => 'Tux'])],
            'invalid JSON in class instantiation'           => [false, json_encode(['Mascott' => 'Tux']), '{"Mascott"::}'],
            'string type not equals number'                 => [false, '{"age": "5"}', '{"age": 5}'],
            'string type not equals boolean'                => [false, '{"age": "true"}', '{"age": true}'],
            'string type not equals null'                   => [false, '{"age": "null"}', '{"age": null}'],
            'object fields are unordered'                   => [true, '{"first":1, "second":"2"}', '{"second":"2", "first":1}'],
            'object fields with numeric keys are unordered' => [true, '{"0":null,"a":{},"b":[],"c":"1","d":1,"e":-1,"f":[1,2],"g":[2,1],"h":{"0":"0","1":"1","2":"2"}}', '{"a":{},"d":1,"b":[],"e":-1,"0":null,"c":"1","f":[1,2],"h":{"2":"2","1":"1","0":"0"},"g":[2,1]}'],
            'child object fields are unordered'             => [true, '{"Mascott": {"name":"Tux", "age":5}}', '{"Mascott": {"age":5, "name":"Tux"}}'],
            'null field different from missing field'       => [false, '{"present": true, "missing": null}', '{"present": true}'],
            'array elements are ordered'                    => [false, '["first", "second"]', '["second", "first"]'],
            'single boolean valid json'                     => [true, 'true', 'true'],
            'single number valid json'                      => [true, '5.3', '5.3'],
            'single null valid json'                        => [true, 'null', 'null'],
            'objects are not arrays'                        => [false, '{}', '[]'],
            'arrays are not objects'                        => [false, '[]', '{}'],
            'objects in arrays are unordered'               => [true, '[{"0":"0","1":"1"},{"2":"2","3":"3"}]', '[{"1":"1","0":"0"},{"2":"2","3":"3"}]'],
        ];
    }

    public static function evaluateThrowsExpectationFailedExceptionWhenJsonIsValidButDoesNotMatchDataprovider(): array
    {
        return [
            'error UTF-8'                                     => [json_encode('\xB1\x31'), json_encode(['Mascott' => 'Tux'])],
            'string type not equals number'                   => ['{"age": "5"}', '{"age": 5}'],
            'string type not equals boolean'                  => ['{"age": "true"}', '{"age": true}'],
            'string type not equals null'                     => ['{"age": "null"}', '{"age": null}'],
            'null field different from missing field'         => ['{"missing": null, "present": true}', '{"present": true}'],
            'array elements are ordered'                      => ['["first", "second"]', '["second", "first"]'],
            'objects with numeric keys are not arrays'        => ['{"0":{}}', '[{}]'],
            'child array elements are ordered'                => ['{"0":null,"a":{},"b":[],"c":"1","d":1,"e":-1,"f":[1,2],"g":[2,1],"h":{"0":"0","1":"1","2":"2"}}',                 '{"a":{},"d":1,"b":[],"e":-1,"0":null,"c":"1","f":[2,1],"h":{"2":"2","1":"1","0":"0"},"g":[2,1]}'],
            'child object with numeric fields stay as object' => ['{"0":null,"a":{},"b":[],"c":"1","d":1,"e":-1,"f":[1,2],"g":[2,1],"h":{"0":"0","1":"1","2":"2"}}', '{"a":{},"d":1,"b":[],"e":-1,"0":null,"c":"1","f":[1,2],"h":["0","1","2"],"g":[2,1]}'],
            'nested arrays are ordered'                       => ['[[1,0],[2,3]]', '[{"1":"1","0":"0"},{"2":"2","3":"3"}]'],
            'child objects in arrays stay in order'           => ['[{"0":"0","1":"1"},{"2":"2","3":"3"}]', '[{"2":"2","3":"3"},{"1":"1","0":"0"}]'],
        ];
    }

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
