--TEST--
#[TestDoxFormatter]: Formatter method does not exist
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/FormatterMethodDoesNotExistTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Formatter Method Does Not Exist (PHPUnit\TestFixture\TestDox\FormatterMethodDoesNotExist)
 ✔ One with data set #0

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\TestDox\FormatterMethodDoesNotExistTest::testOne#0 with data ('string')
Method PHPUnit\TestFixture\TestDox\FormatterMethodDoesNotExistTest::formatter() cannot be used as a TestDox formatter because it does not exist

%sFormatterMethodDoesNotExistTest.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
