--TEST--
GH-873: PHPUnit suppresses exceptions thrown outside of test case function
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/873/Issue873Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
%AException: PHPUnit suppresses exceptions thrown outside of test case function in %s:%i
Stack trace:
%a
