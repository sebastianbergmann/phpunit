--TEST--
phpunit --parallel=2 reports an issue that is triggered while the generator for a parameter of a property is derived for the property, which is checked inside a worker
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/../../property-based-testing/_files/IssueTriggeredWhileGeneratorIsDerivedTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers
Random Seed:   1234

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sClassThatTriggersADeprecationWhenItIsLoaded.php:%d
deprecation triggered when the class is loaded

Triggered by:

* PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredWhileGeneratorIsDerivedTest::testParameterIsOfClassThatTriggersADeprecationWhenItIsLoaded
  %sIssueTriggeredWhileGeneratorIsDerivedTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 3, Deprecations: 1.
