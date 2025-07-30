--TEST--
phpunit --filter testOne --list-tests-xml php://stdout ../../_files/listing-tests-and-groups
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testOne';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../_files/listing-tests-and-groups';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

<?xml version="1.0"?>
<testSuite xmlns="https://xml.phpunit.de/testSuite">
 <tests>
  <testClass name="PHPUnit\TestFixture\ListingTestsAndGroups\ExampleExtendingAbstractTest" file="%sExampleExtendingAbstractTest.php">
   <testMethod id="PHPUnit\TestFixture\ListingTestsAndGroups\ExampleExtendingAbstractTest::testOne" name="testOne"/>
  </testClass>
  <testClass name="PHPUnit\TestFixture\ListingTestsAndGroups\ExampleTest" file="%sExampleTest.php">
   <testMethod id="PHPUnit\TestFixture\ListingTestsAndGroups\ExampleTest::testOne" name="testOne"/>
  </testClass>
 </tests>
 <groups>
  <group name="abstract-one">
   <test id="PHPUnit\TestFixture\ListingTestsAndGroups\ExampleExtendingAbstractTest::testOne"/>
  </group>
  <group name="one">
   <test id="PHPUnit\TestFixture\ListingTestsAndGroups\ExampleTest::testOne"/>
  </group>
 </groups>
</testSuite>%A
