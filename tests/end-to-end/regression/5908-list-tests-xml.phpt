--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5908
--FILE--
<?php declare(strict_types=1);
$file = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = $file;
$_SERVER['argv'][] = __DIR__ . '/5908/Issue5908Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

unlink($file);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

There were errors:

The data provider specified for PHPUnit\TestFixture\Issue5908\Issue5908Test::testOne is invalid
message
