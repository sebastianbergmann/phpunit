--TEST--
phpunit --version
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/OneClassPerFile/wrongClassName/';

require __DIR__ . '/../bootstrap.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Warning:       Test case class not matching filename is deprecated
               in %s/OneClassPerFile/wrongClassName/WrongClassNameTest.php
               Class name was 'WrongClassNameBar', expected 'WrongClassNameTest'

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
