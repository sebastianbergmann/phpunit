--TEST--
phpunit --list-tests-xml php://stdout ../../_files/list-tests --group example
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'example';
$_SERVER['argv'][] = '--list-tests';
$_SERVER['argv'][] = __DIR__ . '/../../_files/list-tests';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test(s):
 - PHPUnit\TestFixture\ListTestsXml\ExampleTest::testOne#0
