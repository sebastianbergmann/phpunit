--TEST--
phpunit --list-tests-xml ../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$xml = tempnam(sys_get_temp_dir(), __FILE__);
file_put_contents($xml, <<<XML
<?xml version="1.0"?>
<tests>
  <!-- This class exists -->
  <testCaseClass name="PHPUnit\TestFixture\DataProviderTest">
    <testCaseMethod name="testAdd" groups="default" dataSet="#0"/>
    <!-- This method does not exist -->
    <testCaseMethod name="methodDoesNotExist"/>
    <!-- name attribute missing -->
    <testCaseMethod />
  </testCaseClass>

  <!-- name attribute missing -->
  <testCaseClass>
    <testCaseMethod name="testAdd" groups="default" dataSet="#0"/>
  </testCaseClass>

  <ignoredTag/>

  <testCaseClass name="Class\Does\Not\Exist">
    <testCaseMethod name="methodAlsoDoesNotExist"/>
  </testCaseClass>
</tests>
XML
);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--tests-xml';
$_SERVER['argv'][3] = $xml;
$_SERVER['argv'][4] = __DIR__ . '/../_files/DataProviderTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main(false);

unlink($xml);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
