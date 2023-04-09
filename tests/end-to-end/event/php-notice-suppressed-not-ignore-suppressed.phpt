--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_NOTICE
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/suppressed-configurations/phpunit-not-ignoring-suppressed-php-notices.xml';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--fail-on-notice';
$_SERVER['argv'][] = __DIR__ . '/_files/SuppressedPhpNoticeTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest::testPhpNotice)
Before Test Method Called (PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest::setUp)
Before Test Method Finished:
- PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest::setUp
Test Prepared (PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest::testPhpNotice)
Test Triggered PHP Notice (PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest::testPhpNotice)
Only variables should be assigned by reference
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest::testPhpNotice)
After Test Method Called (PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest::tearDown)
After Test Method Finished:
- PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest::tearDown
Test Finished (PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest::testPhpNotice)
Test Suite Finished (PHPUnit\TestFixture\Event\SuppressedPhpNoticeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
