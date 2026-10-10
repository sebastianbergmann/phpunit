--TEST--
An issue that is triggered while an example of a property runs, but outside of its lifecycle, is reported for the property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/issue-triggered-outside-of-the-lifecycle-of-an-example/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/issue-triggered-outside-of-the-lifecycle-of-an-example/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with FakeDriver 1.0.0
Configuration: %s
Random Seed:   1234

WW                                                                  2 / 2 (100%)

Time: %s, Memory: %s

2 tests triggered 1 warning:

1) %sFakeDriver.php:%d
warning triggered when the collection of code coverage data is started

Triggered by:

* PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredOutsideOfTheLifecycleOfAnExample\PropertyTest::testProperty (3 times)
  %sPropertyTest.php:%d

* PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredOutsideOfTheLifecycleOfAnExample\PropertyTest::testTest
  %sPropertyTest.php:%d

OK, but there were issues!
Tests: 2, Assertions: 4, Warnings: 1.


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:%w(0/0)
  Methods:%w(0/0)
  Lines:%w(0/0)
