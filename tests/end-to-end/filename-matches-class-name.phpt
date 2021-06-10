--TEST--
phpunit --version
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/OneClassPerFile/wrongClassName/';

require __DIR__ . '/../bootstrap.php';

try {
    PHPUnit\TextUI\Application::main();
} catch (\Exception $e) {
    echo $e->getMessage();
}
?>
--EXPECTF--
Class 'WrongClassNameTest' could not be found in '%sWrongClassNameTest.php'.
