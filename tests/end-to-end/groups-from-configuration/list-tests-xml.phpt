--TEST--
phpunit --configuration _files/phpunit.xml --no-output --list-tests-xml php://stdout
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/phpunit.xml');
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = 'php://stdout';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

<?xml version="1.0"?>
<testSuite xmlns="https://xml.phpunit.de/testSuite">
 <tests>
  <testClass name="PHPUnit\TestFixture\GroupsFromConfiguration\FooTest" file="%sFooTest.php">
   <testMethod id="PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne" name="testOne"/>
  </testClass>
  <phpt file="%sfoo.phpt"/>
  <testClass name="PHPUnit\TestFixture\GroupsFromConfiguration\BarBazTest" file="%sBarBazTest.php">
   <testMethod id="PHPUnit\TestFixture\GroupsFromConfiguration\BarBazTest::testOne" name="testOne"/>
  </testClass>
  <testClass name="PHPUnit\TestFixture\GroupsFromConfiguration\AnotherTest" file="%sAnotherTest.php">
   <testMethod id="PHPUnit\TestFixture\GroupsFromConfiguration\AnotherTest::testOne" name="testOne"/>
  </testClass>
 </tests>
 <groups>
  <group name="another-group">
   <test id="PHPUnit\TestFixture\GroupsFromConfiguration\AnotherTest::testOne"/>
  </group>
  <group name="bar">
   <test id="PHPUnit\TestFixture\GroupsFromConfiguration\BarBazTest::testOne"/>
  </group>
  <group name="baz">
   <test id="PHPUnit\TestFixture\GroupsFromConfiguration\BarBazTest::testOne"/>
  </group>
  <group name="foo">
   <test id="PHPUnit\TestFixture\GroupsFromConfiguration\FooTest::testOne"/>
   <test id="%sfoo.phpt"/>
  </group>
 </groups>
</testSuite>%A
