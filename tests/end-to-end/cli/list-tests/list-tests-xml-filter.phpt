--TEST--
phpunit --filter testOne#0 --list-tests-xml php://stdout ../../_files/list-tests
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare('8.3.0', PHP_VERSION, '>')) {
    print 'skip: PHP < 8.3 required';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testOne#0';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../_files/list-tests';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

<?xml version="1.0"?>
<testSuite xmlns="https://xml.phpunit.de/testSuite">
 <tests>
  <testClass name="PHPUnit\TestFixture\ListTestsXml\AnotherExampleTest" file="%sAnotherExampleTest.php">
   <testMethod id="PHPUnit\TestFixture\ListTestsXml\AnotherExampleTest::testOne" name="testOne"/>
  </testClass>
  <testClass name="PHPUnit\TestFixture\ListTestsXml\ExampleTest" file="%sExampleTest.php">
   <testMethod id="PHPUnit\TestFixture\ListTestsXml\ExampleTest::testOne#0" name="testOne"/>
   <testMethod id="PHPUnit\TestFixture\ListTestsXml\ExampleTest::testTwo" name="testTwo"/>
   <testMethod id="PHPUnit\TestFixture\ListTestsXml\ExampleTest::testThree" name="testThree"/>
  </testClass>
  <phpt file="%sexample.phpt"/>
 </tests>
 <groups>
  <group name="another-example">
   <test id="PHPUnit\TestFixture\ListTestsXml\ExampleTest::testTwo"/>
  </group>
  <group name="default">
   <test id="PHPUnit\TestFixture\ListTestsXml\AnotherExampleTest::testOne"/>
   <test id="PHPUnit\TestFixture\ListTestsXml\ExampleTest::testThree"/>
  </group>
  <group name="example">
   <test id="PHPUnit\TestFixture\ListTestsXml\ExampleTest::testOne#0"/>
  </group>
 </groups>
</testSuite>
The --filter and --list-tests-xml options cannot be combined, --filter is ignored

Wrote list of tests that would have been run to php://stdout
