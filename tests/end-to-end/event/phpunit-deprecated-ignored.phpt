--TEST--
The right events are emitted in the right order for a test that uses a deprecated PHPUnit feature when PHPUnit deprecations are ignored using attribute
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/IgnoredDeprecatedPhpunitFeatureTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\IgnoredDeprecatedPhpunitFeatureTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\IgnoredDeprecatedPhpunitFeatureTest::testDeprecatedPhpunitFeature)
Test Prepared (PHPUnit\TestFixture\Event\IgnoredDeprecatedPhpunitFeatureTest::testDeprecatedPhpunitFeature)
Test Passed (PHPUnit\TestFixture\Event\IgnoredDeprecatedPhpunitFeatureTest::testDeprecatedPhpunitFeature)
Test Finished (PHPUnit\TestFixture\Event\IgnoredDeprecatedPhpunitFeatureTest::testDeprecatedPhpunitFeature)
Test Suite Finished (PHPUnit\TestFixture\Event\IgnoredDeprecatedPhpunitFeatureTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
