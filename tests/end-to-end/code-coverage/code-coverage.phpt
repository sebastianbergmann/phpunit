--TEST--
Add tests for code coverage attributes for class, method, function
--INI--
pcov.directory=tests/end-to-end/code-coverage/code-coverage
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/code-coverage/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s MB

OK (3 tests, 3 assertions)


Code Coverage Report:%w  
  %s
%w
%wSummary:%w
%wClasses: 50.00% (1/2)%w
%wMethods: 75.00% (3/4)%w
%wLines:   66.67% (4/6)%w
%w
%wPHPUnit\TestFixture\CodeCoverage\FullyTestedClass%w
%wMethods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
%wPHPUnit\TestFixture\CodeCoverage\Method
%wMethods:  66.67% ( 2/ 3)   Lines:  66.67% (  2/  3)
