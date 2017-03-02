--TEST--
phpunit --colors=never --coverage-text=php://stdout BankAccountTest ../_files/BankAccountTest.php
--SKIPIF--
<?php
require __DIR__ . '/../bootstrap.php';
if (class_exists('SebastianBergmann\CodeCoverage\CodeCoverage')) {
    print 'skip: phpunit/php-code-coverage must not be installed.';
}
?>
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--colors=never';
$_SERVER['argv'][3] = '--coverage-text=php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = __DIR__ . '/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Error:         phpunit/php-code-coverage is not installed
%A
...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
