--TEST--
#[TestDoxFormatter]: $_dataName parameter receives the dataset name
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/FormatterWithDataNameTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Formatter With Data Name (PHPUnit\TestFixture\TestDox\FormatterWithDataName)
 ✔ first dataset: one
 ✔ second dataset: two

OK (2 tests, 2 assertions)
