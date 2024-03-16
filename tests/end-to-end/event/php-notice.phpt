--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_NOTICE
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--fail-on-notice';
$_SERVER['argv'][] = __DIR__ . '/_files/PhpNoticeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (1 test)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\PhpNoticeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\PhpNoticeTest::testPhpNotice)
Test Prepared (PHPUnit\TestFixture\Event\PhpNoticeTest::testPhpNotice)
Test Triggered PHP Notice (PHPUnit\TestFixture\Event\PhpNoticeTest::testPhpNotice)
Only variables should be assigned by reference
Test Triggered PHP Notice (PHPUnit\TestFixture\Event\PhpNoticeTest::testPhpNotice, suppressed using operator)
Only variables should be assigned by reference
Test Passed (PHPUnit\TestFixture\Event\PhpNoticeTest::testPhpNotice)
Test Finished (PHPUnit\TestFixture\Event\PhpNoticeTest::testPhpNotice)
Test Suite Finished (PHPUnit\TestFixture\Event\PhpNoticeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
