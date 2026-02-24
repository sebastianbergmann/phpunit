--TEST--
phpunit --test-files-file test_files_broken
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-files-file';
$_SERVER['argv'][] = '--test-files-file';
$_SERVER['argv'][] = __DIR__ . '/_files/test-files-file/test_files_broken.txt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test file "tests/FourTest.php" not found
