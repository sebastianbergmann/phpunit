--TEST--
phpunit --test-id-filter-file nonexistent.txt
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--test-id-filter-file';
$_SERVER['argv'][] = 'nonexistent.txt';
$_SERVER['argv'][] = __DIR__ . '/../../_files/test-id-filter/tests/FooTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test ID filter file "nonexistent.txt" not found
