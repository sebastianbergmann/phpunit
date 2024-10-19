--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5844
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/5844/bootstrap.php';
$_SERVER['argv'][] = __DIR__ . '/5844/Issue5844Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%s5844%sbootstrap.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5844\Issue5844Test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5844\Issue5844Test::testOne)
Test Considered Risky (PHPUnit\TestFixture\Issue5844\Issue5844Test::testOne)
At least one error handler is not callable outside the scope it was registered in
Test Prepared (PHPUnit\TestFixture\Issue5844\Issue5844Test::testOne)
Test Passed (PHPUnit\TestFixture\Issue5844\Issue5844Test::testOne)
Test Finished (PHPUnit\TestFixture\Issue5844\Issue5844Test::testOne)
Test Suite Finished (PHPUnit\TestFixture\Issue5844\Issue5844Test, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
