--TEST--
phpunit --exclude-group one --list-tests ../../_files/listing-tests-and-groups
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--exclude-group';
$_SERVER['argv'][] = 'one';
$_SERVER['argv'][] = '--list-tests';
$_SERVER['argv'][] = __DIR__ . '/../../_files/listing-tests-and-groups';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available tests:
 - PHPUnit\TestFixture\ListingTestsAndGroups\ExampleExtendingAbstractTest::testOne
 - PHPUnit\TestFixture\ListingTestsAndGroups\ExampleTest::testTwo
 - PHPUnit\TestFixture\ListingTestsAndGroups\ExampleTest::testThree
 - %sexample.phpt
