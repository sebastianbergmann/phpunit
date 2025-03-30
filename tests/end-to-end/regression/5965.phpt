--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5965
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pdo')) {
    print 'skip: Extension PDO must be loaded.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/5965/Issue5965Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5891\Issue5965Test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5891\Issue5965Test::testOne)
Test Prepared (PHPUnit\TestFixture\Issue5891\Issue5965Test::testOne)
Test Errored (PHPUnit\TestFixture\Issue5891\Issue5965Test::testOne)
(exception code: HY000)
Test Finished (PHPUnit\TestFixture\Issue5891\Issue5965Test::testOne)
Test Suite Finished (PHPUnit\TestFixture\Issue5891\Issue5965Test, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
