--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5764
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\Issue5764\Issue5764Test".

No tests executed!
