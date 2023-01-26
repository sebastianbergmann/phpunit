--TEST--
phpunit --configuration ../_files/multiple-testsuites/phpunit.xml --exclude-testsuite end-to-end
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/multiple-testsuites/phpunit.xml';
$_SERVER['argv'][] = '--exclude-testsuite';
$_SERVER['argv'][] = 'end-to-end';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
