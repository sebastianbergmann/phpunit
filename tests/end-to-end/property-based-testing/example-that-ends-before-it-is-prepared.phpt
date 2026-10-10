--TEST--
An example that fails or errors before it is prepared does not falsify the property and is reported without being shrunk
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ExampleThatEndsBeforeItIsPreparedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

EF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::testErrorInSetUp
RuntimeException: error in setUp()

%sExampleThatEndsBeforeItIsPreparedTest.php:%d

--

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::testFailureInSetUp
failure in setUp()

%sExampleThatEndsBeforeItIsPreparedTest.php:%d

ERRORS!
Tests: 2, Assertions: 1, Errors: 1, Failures: 1.
