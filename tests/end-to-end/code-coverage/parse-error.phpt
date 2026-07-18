--TEST--
A warning is emitted for a file in the code coverage report that cannot be parsed for static analysis
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/parse-error/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/parse-error/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with CustomDriverWithFakeData 1.0.0
Configuration: %s

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Cannot parse %sCannotBeParsed.php (Syntax error, unexpected ';' on line 3), code coverage for this file is based on raw data reported by the code coverage driver

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.


Code Coverage Report:%w
  %s

 Summary:%w
  Classes: 100.00% (1/1)
  Methods: 100.00% (1/1)
  Lines:   100.00% (3/3)

PHPUnit\TestFixture\CodeCoverageParseError\Foo
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
