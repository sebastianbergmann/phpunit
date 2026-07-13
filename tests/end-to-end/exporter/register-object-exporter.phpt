--TEST--
An object exporter registered using TestCase::registerObjectExporter() is used for failure output and unregistered after the test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/bootstrap.php';
$_SERVER['argv'][] = __DIR__ . '/_files/ObjectExporterTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

FFF                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There were 3 failures:

1) PHPUnit\TestFixture\ObjectExporter\ObjectExporterTest::testCustomObjectExporterIsUsedForConstraintFailureDescription
Failed asserting that an array contains Message ("hello").

%sObjectExporterTest.php:%d

2) PHPUnit\TestFixture\ObjectExporter\ObjectExporterTest::testCustomObjectExporterIsUsedForComparisonFailure
stdClass Object #%d () is not instance of expected class "PHPUnit\TestFixture\ObjectExporter\Message".
--- Expected
+++ Actual
@@ @@
-Message ("hello")
+stdClass Object #%d ()

%sObjectExporterTest.php:%d

3) PHPUnit\TestFixture\ObjectExporter\ObjectExporterTest::testDefaultExportIsUsedWhenNoObjectExporterIsRegistered
Failed asserting that an array contains PHPUnit\TestFixture\ObjectExporter\Message Object #%d (
    'text' => 'hello',
    'payload' => Array &0 [
        0 => 1,
        1 => 2,
        2 => 3,
    ],
).

%sObjectExporterTest.php:%d

FAILURES!
Tests: 3, Assertions: 3, Failures: 3.
