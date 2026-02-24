--TEST--
phpunit --test-files-file does_not_exit.txt
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-files-file';
$_SERVER['argv'][] = '--test-files-file';
$_SERVER['argv'][] = __DIR__ . '/_files/test-files-file/does_not_exit.txt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot read from %sdoes_not_exit.txt
