--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5908
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-tests';
$_SERVER['argv'][] = __DIR__ . '/5908/Issue5908Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

There were errors:

The data provider specified for PHPUnit\TestFixture\Issue5908\Issue5908Test::testOne is invalid
message
