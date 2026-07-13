--TEST--
An object exporter registered using TestCase::registerObjectExporter() is used for failure output when the test is run in a separate process
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/bootstrap.php';
$_SERVER['argv'][] = __DIR__ . '/_files/ObjectExporterIsolationTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ObjectExporter\ObjectExporterIsolationTest::testCustomObjectExporterIsUsedInSeparateProcess
Failed asserting that an array contains Message ("hello").

%sObjectExporterIsolationTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
