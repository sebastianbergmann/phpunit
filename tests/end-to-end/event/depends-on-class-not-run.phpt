--TEST--
The right events are emitted in the right order for a test that depends on a class that was not run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsOnClassNotRunTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\DependsOnClassNotRunTest, 1 test)
Test Skipped (PHPUnit\TestFixture\Event\DependsOnClassNotRunTest::testOne)
This test depends on "PHPUnit\TestFixture\Event\DependsOnClassNotRunTarget::class" to pass
Test Suite Finished (PHPUnit\TestFixture\Event\DependsOnClassNotRunTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
