--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3904
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][3] = 'Issue3904';
$_SERVER['argv'][4] = __DIR__ . '/3904/Issue3904_2Test.php';

require __DIR__ . '/../../../bootstrap.php';

try {
    PHPUnit\TextUI\Command::main();
} catch (\Exception $e) {
    echo $e->getMessage();
}
?>
--EXPECTF--
Class 'Issue3904' could not be found in '%s'.
