--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6511
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6511/Issue6511Test.php';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Issue6511 (PHPUnit\TestFixture\Issue6511\Issue6511)
 ✔ The a is Alfa, the b is Bravo, the c is Charlie.

OK (1 test, 3 assertions)
