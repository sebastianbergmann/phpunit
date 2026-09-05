--TEST--
Deriving test impact data from code coverage targets that are not checked is warned about
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-coverage-targets-that-are-not-checked.xml';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.coverage-targets-that-are-not-checked');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Test impact data derived from code coverage targets is only as complete as those targets are, and they are not checked because tests that execute code they do not declare are not considered risky

OK, but there were issues!
Tests: 2, Assertions: 2, PHPUnit Warnings: 1.
