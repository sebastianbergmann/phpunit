--TEST--
phpunit --colors=never --coverage-text=php://stdout --disable-ignore-coverage IgnoreCodeCoverageClassTest ../_files/IgnoreCodeCoverageClassTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--colors=never';
$_SERVER['argv'][3] = '--coverage-text=php://stdout';
$_SERVER['argv'][4] = '--disable-ignore-coverage';
$_SERVER['argv'][5] = __DIR__.'/../_files/IgnoreCodeCoverageClassTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.
Warning:	No whitelist configured for code coverage

..                                                                  2 / 2 (100%)

Time: %s, Memory: %sMb

OK (2 tests, 2 assertions)


Code Coverage Report:%w
%s
%w
 Summary:%w
  Classes: 100.00% (1/1)%w
  Methods: 100.00% (2/2)%w
  Lines:%s

IgnoreCodeCoverageClass
  Methods: 100.00% ( 2/ 2)   Lines: 100.00% (  2/  2)