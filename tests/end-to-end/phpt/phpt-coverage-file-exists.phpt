--TEST--
Error when code coverage file exists
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-coverage-file-exists/test.phpt');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
Fatal error: Uncaught PHPUnit\Runner\Exception: File %stest.coverage exists, PHPT test %stest.phpt will not be executed%A
