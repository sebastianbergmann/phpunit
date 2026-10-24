--TEST--
Groups that are declared on a parent class of a test class are listed by --list-tests-xml
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/group-attributes-on-parent-class';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

<?xml version="1.0"?>
<testSuite xmlns="https://xml.phpunit.de/testSuite">
 <tests>
  <testClass name="PHPUnit\TestFixture\GroupAttributesOnParentClass\FirstTest" file="%sFirstTest.php">
   <testMethod id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FirstTest::testOne" name="testOne"/>
  </testClass>
  <testClass name="PHPUnit\TestFixture\GroupAttributesOnParentClass\FourthTest" file="%sFourthTest.php">
   <testMethod id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FourthTest::testOne" name="testOne"/>
  </testClass>
  <testClass name="PHPUnit\TestFixture\GroupAttributesOnParentClass\SecondTest" file="%sSecondTest.php">
   <testMethod id="PHPUnit\TestFixture\GroupAttributesOnParentClass\SecondTest::testOne" name="testOne"/>
  </testClass>
  <testClass name="PHPUnit\TestFixture\GroupAttributesOnParentClass\ThirdTest" file="%sThirdTest.php">
   <testMethod id="PHPUnit\TestFixture\GroupAttributesOnParentClass\ThirdTest::testOne" name="testOne"/>
  </testClass>
 </tests>
 <groups>
  <group name="1234">
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FirstTest::testOne"/>
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FourthTest::testOne"/>
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\SecondTest::testOne"/>
  </group>
  <group name="large">
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FirstTest::testOne"/>
  </group>
  <group name="medium">
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\ThirdTest::testOne"/>
  </group>
  <group name="one">
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FirstTest::testOne"/>
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FourthTest::testOne"/>
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\SecondTest::testOne"/>
  </group>
  <group name="small">
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FourthTest::testOne"/>
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\SecondTest::testOne"/>
  </group>
  <group name="two">
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FirstTest::testOne"/>
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\FourthTest::testOne"/>
   <test id="PHPUnit\TestFixture\GroupAttributesOnParentClass\SecondTest::testOne"/>
  </group>
 </groups>
</testSuite>%A
