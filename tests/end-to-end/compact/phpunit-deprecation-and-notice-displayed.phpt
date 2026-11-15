--TEST--
phpunit --compact --display-phpunit-deprecations --display-phpunit-notices ../_files/PhpunitDeprecationAndNoticeTest
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = '--display-phpunit-notices';
$_SERVER['argv'][] = __DIR__ . '/../_files/PhpunitDeprecationAndNoticeTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

OK (3 tests, 3 assertions, 1 PHPUnit deprecation, 1 PHPUnit notice)

--- PHPUNIT DEPRECATION: PHPUnit\TestFixture\PhpunitDeprecationAndNoticeTest::testTriggersPhpunitDeprecation
deprecation message

--- PHPUNIT NOTICE: PHPUnit\TestFixture\PhpunitDeprecationAndNoticeTest::testTriggersPhpunitNotice
notice message

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --no-configuration --compact --display-phpunit-deprecations --display-phpunit-notices %s
Working directory: %s
Configuration: (none)
Bootstrap: (none)
Extensions: (none)
Started: %s
Ended: %s (completed)
Shell exit code: 0
Tests: 3 run, 3 assertions
Issues triggered by tests: 1 PHPUnit deprecation, 1 PHPUnit notice
Issues triggered by the test runner: (none)
