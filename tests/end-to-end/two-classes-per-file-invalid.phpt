--TEST--
phpunit --version
--FILE--
<?php
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/OneClassPerFile/failing/';

require __DIR__ . '/../bootstrap.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Warning: Test case class not matching filename is deprecated
         in %s/OneClassPerFile/failing/TwoClassesInvalidTest.php
         see #4105, class name was 'TwoClassesInvalid', expected 'TwoClassesInvalidTest'
Warning: Test case class not matching filename is deprecated
         in %s/OneClassPerFile/failing/TwoClassesInvalidTest.php
         see #4105, class name was 'TwoClassesInvalid2', expected 'TwoClassesInvalidTest'
Warning: Multiple test case classes per file is deprecated (in "%s/TwoClassesInvalidTest.php", see #4105)

..                                                                  2 / 2 (100%)

Time: %d ms, Memory: %f MB

OK (2 tests, 2 assertions)
