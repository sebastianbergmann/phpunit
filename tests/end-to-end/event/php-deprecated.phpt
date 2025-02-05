--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_DEPRECATED
--INI--
error_reporting=-1
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--fail-on-deprecation';
$_SERVER['argv'][] = __DIR__ . '/_files/DeprecatedPhpFeatureTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
Test Prepared (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
Test Triggered PHP Deprecation (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature, unknown if issue was triggered in first-party code or third-party code) in %s:%d
strlen(): Passing null to parameter #1 ($string) of type string is deprecated
Test Triggered PHP Deprecation (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature, unknown if issue was triggered in first-party code or third-party code, suppressed using operator) in %s:%d
strlen(): Passing null to parameter #1 ($string) of type string is deprecated
Test Passed (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
Test Finished (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest::testDeprecatedPhpFeature)
Test Suite Finished (PHPUnit\TestFixture\Event\DeprecatedPhpFeatureTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
