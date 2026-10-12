--TEST--
A warning is emitted when code coverage cannot be collected in the child process of a test that is run in process isolation
--SKIPIF--
<?php declare(strict_types=1);
if (!function_exists('xdebug_info') || !in_array('coverage', xdebug_info('mode'), true)) {
    print 'skip: This test requires Xdebug with code coverage enabled';
}
--INI--
pcov.enabled=0
xdebug.mode=coverage
--FILE--
<?php declare(strict_types=1);
// The XDEBUG_MODE environment variable, which the child process inherits,
// takes precedence over the xdebug.mode setting forwarded to it
putenv('XDEBUG_MODE=off');

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/process-isolation-code-coverage-driver-missing-in-child-process';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/process-isolation-code-coverage-driver-missing-in-child-process/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 2 PHPUnit test runner warnings:

1) XDEBUG_MODE=coverage (environment variable) or xdebug.mode=coverage (PHP configuration setting) has to be set

2) Code coverage could not be collected for PHPUnit\TestFixture\ProcessIsolationCodeCoverageDriverMissingInChildProcess\CoveredTest::testSomething

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 2.


Code Coverage Report:
  %s

 Summary:
  Classes:  0.00% (0/1)
  Methods:  0.00% (0/1)
  Lines:    0.00% (0/1)
