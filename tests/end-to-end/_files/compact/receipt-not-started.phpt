--TEST--
The receipt in the compact output says that the test run was not started when code coverage cannot be collected
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug')) {
    print 'skip: Extension xdebug must be loaded.';
}
--ENV--
XDEBUG_MODE=debug
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--do-not-fail-on-phpunit-warning';
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__ . '/../basic/unit';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = __DIR__ . '/../basic/unit/StatusTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

No tests executed!

--- PHPUNIT TEST RUNNER WARNING
XDEBUG_MODE=coverage (environment variable) or xdebug.mode=coverage (PHP configuration setting) has to be set

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --no-configuration --compact --do-not-fail-on-phpunit-warning --coverage-filter %s --coverage-text %s
Working directory: %s
Configuration: (none)
Bootstrap: (none)
Extensions: (none)
Started: %s
Ended: %s (not-started)
Shell exit code: 0
Tests: 0 run, 0 assertions
Issues triggered by tests: (none)
Issues triggered by the test runner: 1 PHPUnit warning
