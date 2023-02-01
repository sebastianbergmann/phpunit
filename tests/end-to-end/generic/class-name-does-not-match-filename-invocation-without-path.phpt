--TEST--
phpunit --version
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/OneClassPerFile/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
?>
--XFAIL--
https://github.com/sebastianbergmann/phpunit/issues/5074
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Warning:       Test case class not matching filename is deprecated
               in %sWrongClassNameTest.php
               Class name was 'WrongClassNameBar', expected 'WrongClassNameTest'

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
