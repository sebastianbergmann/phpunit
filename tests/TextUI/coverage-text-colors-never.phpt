--TEST--
phpunit --configuration=../_files/configuration.coverage-text.only-summary.colors-never.xml CoveredClass ../_files/CoverageClassTest.php
--SKIPIF--
<?php
if (PHP_MAJOR_VERSION == 7) {
    print 'skip: PHP 7 has no code coverage driver';
}
?>
--FILE--
<?php
$_SERVER['argv'][1] = '--configuration=' . dirname(__FILE__) . '/../_files/configuration.coverage-text.only-summary.colors-never.xml';
$_SERVER['argv'][2] = 'CoveredClass';
$_SERVER['argv'][3] = __DIR__ . '/../_files/CoverageClassTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s ms, Memory: %sMb

OK (1 test, 0 assertions)


Code Coverage Report Summary:
  Classes: 50.00% (1/2)%s
  Methods: 50.00% (3/6)%s
  Lines:   58.33% (7/12)
