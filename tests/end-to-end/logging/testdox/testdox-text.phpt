--TEST--
phpunit --testdox-text php://stdout ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$output = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-text';
$_SERVER['argv'][] = $output;
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../../_files/BankAccountTest.php');

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($output);

unlink($output);
--EXPECTF--
Bank Account (PHPUnit\TestFixture\BankAccount)
 [x] Balance is initially zero
 [x] Balance cannot become negative
