--TEST--
The right events are emitted in the right order when a PHPUnit extension from a PHAR is loaded
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
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/phar-extension';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (1 test)
Test Runner Started
Extension Loaded from PHAR (phpunit/phpunit-test-extension 1.0.0)
Test Suite Sorted
Event Facade Sealed
Test Runner Execution Started (1 test)
Test Suite Started (1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\TestExtension\ExtensionTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\TestExtension\ExtensionTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestExtension\ExtensionTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\TestExtension\ExtensionTest::testOne)
Test Finished (PHPUnit\TestFixture\TestExtension\ExtensionTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\TestExtension\ExtensionTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
