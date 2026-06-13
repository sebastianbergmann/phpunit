--TEST--
DataProvider: dependent test is not blocked when a method with the same name fails in another class
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/DependsBlockedByOtherClassFailingTest.php';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/DependsBlockedByOtherClassDependingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F...                                                                4 / 4 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\DataProvider\DependsBlockedByOtherClassFailingTest::testSomething
Failure in unrelated class

%sDependsBlockedByOtherClassFailingTest.php:%d

FAILURES!
Tests: 4, Assertions: 4, Failures: 1.
