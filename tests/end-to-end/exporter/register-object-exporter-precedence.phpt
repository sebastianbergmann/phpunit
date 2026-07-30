--TEST--
The object exporter registered last using TestCase::registerObjectExporter() takes precedence over the ones registered before it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/bootstrap-precedence.php';
$_SERVER['argv'][] = __DIR__ . '/_files/ObjectExporterPrecedenceTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

FF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\ObjectExporter\ObjectExporterPrecedenceTest::testMostRecentlyRegisteredObjectExporterTakesPrecedence
Failed asserting that an array contains Alternative Message ("hello").

%sObjectExporterPrecedenceTest.php:%d

2) PHPUnit\TestFixture\ObjectExporter\ObjectExporterPrecedenceTest::testPreviouslyRegisteredObjectExporterIsUsedForObjectsTheMostRecentlyRegisteredOneDoesNotHandle
Failed asserting that an array contains Message ("goodbye").

%sObjectExporterPrecedenceTest.php:%d

FAILURES!
Tests: 2, Assertions: 2, Failures: 2.
