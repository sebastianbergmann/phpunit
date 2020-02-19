--TEST--
phpunit --version
--FILE--
<?php
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/OneClassPerFile/wrongClassName/';

require __DIR__ . '/../bootstrap.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Warning: Test case class not matching filename is deprecated
         in %s/OneClassPerFile/wrongClassName/WrongClassNameTest.php
         class name was 'WrongClassNameBar', expected 'WrongClassNameTest', see #4105

.                                                                   1 / 1 (100%)

Time: %d ms, Memory: %f MB

OK (1 test, 1 assertion)
