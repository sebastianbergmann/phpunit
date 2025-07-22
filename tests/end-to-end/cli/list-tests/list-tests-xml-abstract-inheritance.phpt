--TEST--
phpunit --list-tests-xml abstract inheritance
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../../_files/abstract/without-test-suffix';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

<?xml version="1.0"?>
<tests>
 <testCaseClass name="PHPUnit\TestFixture\ConcreteTestClassExtendingAbstractTestClassWithoutTestSuffixTest" file="%sConcreteTestClassExtendingAbstractTestClassWithoutTestSuffixTest.php">
  <testCaseMethod id="PHPUnit\TestFixture\ConcreteTestClassExtendingAbstractTestClassWithoutTestSuffixTest::testOne" name="testOne" groups="default"/>
 </testCaseClass>
</tests>%A 