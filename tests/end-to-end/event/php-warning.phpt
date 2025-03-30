--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_WARNING
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--fail-on-warning';
$_SERVER['argv'][] = __DIR__ . '/_files/PhpWarningTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\PhpWarningTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\PhpWarningTest::testPhpWarning)
Test Prepared (PHPUnit\TestFixture\Event\PhpWarningTest::testPhpWarning)
Test Triggered PHP Warning (PHPUnit\TestFixture\Event\PhpWarningTest::testPhpWarning) in %s:%d
Undefined variable $b
Test Triggered PHP Warning (PHPUnit\TestFixture\Event\PhpWarningTest::testPhpWarning, suppressed using operator) in %s:%d
Undefined variable $b
Test Passed (PHPUnit\TestFixture\Event\PhpWarningTest::testPhpWarning)
Test Finished (PHPUnit\TestFixture\Event\PhpWarningTest::testPhpWarning)
Test Suite Finished (PHPUnit\TestFixture\Event\PhpWarningTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
