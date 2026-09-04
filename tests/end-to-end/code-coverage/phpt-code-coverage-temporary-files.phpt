--TEST--
The temporary files used for collecting code coverage for a PHPT test are not created in the directory the PHPT file is in
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';
--INI--
pcov.directory=tests/end-to-end/code-coverage/_files/phpt-code-coverage
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpt-code-coverage';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/phpt-code-coverage/.phpunit.cache');
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

PHPUnit\TestFixture\PhptCodeCoverage\Foo
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
