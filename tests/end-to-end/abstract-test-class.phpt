--TEST--
phpunit ../../_files/AbstractTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/AbstractTest.php';

require __DIR__ . '/../bootstrap.php';
try {
    PHPUnit\TextUI\Command::main();
} catch (\Exception $e) {
    echo $e->getMessage();
}    
--EXPECTF--
Class 'AbstractTest' could not be found in '%s/tests/_files/AbstractTest.php'.
