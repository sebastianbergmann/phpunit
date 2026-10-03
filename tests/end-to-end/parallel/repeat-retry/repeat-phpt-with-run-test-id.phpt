--TEST--
phpunit --repeat 3 --run-test-id runs only the selected repetition of a PHPT test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--run-test-id';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/selected-repetition.phpt') . ' (repetition 2 of 3)';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/selected-repetition.phpt');

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
