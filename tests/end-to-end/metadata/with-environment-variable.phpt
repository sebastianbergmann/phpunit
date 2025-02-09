--TEST--
Tests are correctly ran based on environment variables requirements
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/with_environment_variable/phpunit.xml';
$_SERVER['argv'][] = '--display-skipped';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

...............                                                   15 / 15 (100%)

Time: %s, Memory: %s

OK (15 tests, 42 assertions)

