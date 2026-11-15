--TEST--
phpunit --parallel=2 reports the error for a method that cannot be used as a TestDox formatter once, not once for the main process and once for the worker that runs the test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/../../testdox/_files/FormatterMethodDoesNotExistTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

Time: %s, Memory: %s

Formatter Method Does Not Exist (PHPUnit\TestFixture\TestDox\FormatterMethodDoesNotExist)
 ✔ One with data set #0

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\TestDox\FormatterMethodDoesNotExistTest::testOne#0 with data ('string')
Method PHPUnit\TestFixture\TestDox\FormatterMethodDoesNotExistTest::formatter() cannot be used as a TestDox formatter because it does not exist

%sFormatterMethodDoesNotExistTest.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
