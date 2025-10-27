--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit.xml';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s/tests/end-to-end/repeat/_files/phpunit.xml

....                                                                4 / 4 (100%)

Time: %s, Memory: %s MB

OK (4 tests, 4 assertions)
