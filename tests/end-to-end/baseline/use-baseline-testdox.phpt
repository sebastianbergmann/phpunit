--TEST--
phpunit --configuration ../_files/baseline/use-baseline/phpunit.xml --testdox
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/use-baseline/phpunit.xml';
$_SERVER['argv'][] = '--testdox';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.....                                                               5 / 5 (100%)

Time: %s, Memory: %s

Source (PHPUnit\TestFixture\Baseline\Source)
 ✔ Deprecation
 ✔ Notice
 ✔ Warning
 ✔ Php deprecation
 ✔ Php notice and warning

OK (5 tests, 5 assertions)

6 issues were ignored by baseline.
