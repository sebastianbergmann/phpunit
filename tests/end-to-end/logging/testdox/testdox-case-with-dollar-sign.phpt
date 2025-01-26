--TEST--
Testdox: output containing dollar signs in the value from data provider
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/../_files/CaseWithDollarSignTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

Case With Dollar Sign (PHPUnit\TestFixture\CaseWithDollarSign)
 ✔ The "$12.34" is used for this test
 ✔ The "Some text before the price $5.67" is used for this test
 ✔ The "Dollar sign followed by letter $Q" is used for this test
 ✔ The "Alone $ surrounded by spaces" is used for this test

OK (4 tests, 4 assertions)
