--TEST--
An issue that is triggered while the generator for a parameter of a property is derived is reported once for the property, not for each of its examples
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/_files/IssueTriggeredWhileGeneratorIsDerivedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
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
