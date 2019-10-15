--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3889
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = __DIR__ . '/3889/MyIssue3889Test.php';

require __DIR__ . '/../../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
Fatal error: Uncaught PHPUnit\Runner\Exception: Class 'MyIssue3889Test' could not be found in %s
%s
%s
%s
%s
%s
%s
%s
%s
