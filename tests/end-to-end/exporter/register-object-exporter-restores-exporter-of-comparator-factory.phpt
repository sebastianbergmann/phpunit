--TEST--
An exporter that was configured on the comparator factory before the test run is restored after a test that registered an object exporter using TestCase::registerObjectExporter()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/bootstrap-comparator-factory.php';
$_SERVER['argv'][] = __DIR__ . '/_files/ExporterOfComparatorFactoryTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

FF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\ObjectExporter\ExporterOfComparatorFactoryTest::testObjectExporterRegisteredByTestTakesPrecedenceOverExporterOfComparatorFactory
stdClass Object #%d () is not instance of expected class "PHPUnit\TestFixture\ObjectExporter\Message".
--- Expected
+++ Actual
@@ @@
-Message ("hello")
+stdClass Object #%d ()

%sExporterOfComparatorFactoryTest.php:%d

2) PHPUnit\TestFixture\ObjectExporter\ExporterOfComparatorFactoryTest::testExporterOfComparatorFactoryIsRestoredAfterTestThatRegisteredObjectExporter
stdClass Object #%d () is not instance of expected class "PHPUnit\TestFixture\ObjectExporter\Message".
--- Expected
+++ Actual
@@ @@
-Bootstrap Message ("hello")
+stdClass Object #%d ()

%sExporterOfComparatorFactoryTest.php:%d

FAILURES!
Tests: 2, Assertions: 2, Failures: 2.
