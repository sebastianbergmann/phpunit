--TEST--
Exclude single test suite using --exclude-testsuite
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/multiple-testsuites/phpunit.xml';
$_SERVER['argv'][] = '--exclude-testsuite';
$_SERVER['argv'][] = 'unit';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (end-to-end, 1 test)
Test Suite Started (PHPUnit\TestFixture\BarTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\BarTest::testOne)
Test Prepared (PHPUnit\TestFixture\BarTest::testOne)
Test Passed (PHPUnit\TestFixture\BarTest::testOne)
Test Finished (PHPUnit\TestFixture\BarTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\BarTest, 1 test)
Test Suite Finished (end-to-end, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
