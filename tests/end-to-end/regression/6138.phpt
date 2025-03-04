--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6138
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6138/Issue6138Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue6138\Issue6138Test::testOne
Expectation failed for method name is "m" when invoked 1 time
Parameter 0 for invocation PHPUnit\TestFixture\Issue6138\I::m(PHPUnit\TestFixture\Issue6138\C Object (...)): void does not match expected value.
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 PHPUnit\TestFixture\Issue6138\C Object (
-    'foo' => 'bar'
+    'foo' => 'baz'
 )

%sIssue6138Test.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
