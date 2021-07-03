--TEST--
phpunit --no-output ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);

print 'placeholder' . PHP_EOL;

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/BankAccountTest.php');

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
placeholder
