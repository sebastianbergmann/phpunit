--TEST--
phpunit --filter testOne#0 --list-tests ../../_files/list-tests
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testOne#0';
$_SERVER['argv'][] = '--list-tests';
$_SERVER['argv'][] = __DIR__ . '/../../_files/list-tests';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

The --filter and --list-tests options cannot be combined, --filter is ignored

Available test(s):
 - PHPUnit\TestFixture\ListTestsXml\AnotherExampleTest::testOne
 - PHPUnit\TestFixture\ListTestsXml\ExampleTest::testOne#0
 - PHPUnit\TestFixture\ListTestsXml\ExampleTest::testTwo
 - PHPUnit\TestFixture\ListTestsXml\ExampleTest::testThree
 - %sexample.phpt
