--TEST--
phpunit --list-test-files list-phpt-test-files.phpt
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--list-test-files';
$_SERVER['argv'][] = __DIR__.'/list-phpt-test-files.phpt';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test files:
 - %send-to-end%slist-phpt-test-files.phpt
