--TEST--
phpunit --display-incomplete --display-skipped ../_files/MarkedIncompleteInBeforeClassMethodOfTestClassWhoseTestsAllRunInSeparateProcessesTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-incomplete';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = __DIR__ . '/../_files/MarkedIncompleteInBeforeClassMethodOfTestClassWhoseTestsAllRunInSeparateProcessesTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

SS                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 skipped tests:

1) PHPUnit\TestFixture\MarkedIncompleteInBeforeClassMethodOfTestClassWhoseTestsAllRunInSeparateProcessesTest::testOne
message

2) PHPUnit\TestFixture\MarkedIncompleteInBeforeClassMethodOfTestClassWhoseTestsAllRunInSeparateProcessesTest::testTwo
message

OK, but some tests were skipped!
Tests: 2, Assertions: 0, Skipped: 2.
