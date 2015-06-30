--TEST--
phpunit --configuration=../TextUI/ignore-coverage-text-summary-only-without-colors.xml FullCoverageClassTest ../_files/IgnoreCoverageClassTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--configuration=' . dirname(__FILE__) . '/../TextUI/ignore-coverage-text-summary-only-without-colors.xml';
$_SERVER['argv'][2] = 'IgnoreCoverageClassTest';
$_SERVER['argv'][3] = dirname(__FILE__).'/../_files/IgnoreCoverageClassTest.php';

require __DIR__ . '/../bootstrap.php';

PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.

Time: %s, Memory: %sMb

OK (1 test, 0 assertions)


Code Coverage Report Summary:
  Classes: 100.00% (1/1)     
  Methods:        (0/0)      
  Lines:          (0/0)     
