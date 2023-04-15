--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_DEPRECATED
--SKIPIF--
<?php declare(strict_types=1);
if (!(PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION === 1)) {
    print "skip: this test requires PHP 8.1\n";
}

if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--INI--
error_reporting=-1
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--fail-on-deprecation';
$_SERVER['argv'][] = __DIR__ . '/_files/DeprecatedPhpFeatureTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
Test Prepared (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
Test Triggered PHP Deprecation (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
defined(): Passing null to parameter #1 ($constant_name) of type string is deprecated
Test Triggered Suppressed PHP Deprecation (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
defined(): Passing null to parameter #1 ($constant_name) of type string is deprecated
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
Test Finished (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
Test Suite Finished (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
