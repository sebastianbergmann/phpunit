--TEST--
An object exporter registered using TestCase::registerObjectExporter() is used for every occurrence of an object, not only for its first occurrence
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/bootstrap.php';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatedOccurrenceTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ObjectExporter\RepeatedOccurrenceTest::testEveryOccurrenceOfObjectIsExportedByRegisteredObjectExporter
Failed asserting that two arrays are identical.
--- Expected
+++ Actual
@@ @@
-Array &0 []
+Array &0 [
+    0 => Message ("hello"),
+    1 => Message ("hello"),
+]

%sRepeatedOccurrenceTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
