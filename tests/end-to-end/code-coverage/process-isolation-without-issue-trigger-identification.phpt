--TEST--
The source map is still shared with a child process when the identification of issue triggers is disabled but code coverage is collected
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';
--INI--
pcov.directory=tests/end-to-end/code-coverage/_files/process-isolation-without-issue-trigger-identification
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/process-isolation-without-issue-trigger-identification';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/process-isolation-without-issue-trigger-identification/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)


Code Coverage Report:
  %s

 Summary:
  Classes: 100.00% (1/1)
  Methods: 100.00% (1/1)
  Lines:   100.00% (1/1)

PHPUnit\TestFixture\ProcessIsolationWithoutIssueTriggerIdentification\Covered
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
