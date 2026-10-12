--TEST--
A warning is emitted when code coverage cannot be collected in the child process of a test that is run in process isolation
--FILE--
<?php declare(strict_types=1);
// The configured code coverage driver is only available in the process with this ID
putenv('PHPUNIT_TEST_MAIN_PROCESS_ID=' . getmypid());

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/code-coverage-driver-not-available-in-child-process/phpunit-process-isolation.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/code-coverage-driver-not-available-in-child-process/.phpunit.cache.process-isolation');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverThatIsOnlyAvailableInMainProcess 1.0.0
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 2 PHPUnit test runner warnings:

1) No code coverage driver available that supports line coverage

2) Code coverage could not be collected for PHPUnit\TestFixture\CodeCoverageDriverNotAvailableInChildProcess\CoveredTest::testSomething

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 2.


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:    0.00% (0/1)
  Methods:    0.00% (0/1)
  Lines:      0.00% (0/1)
