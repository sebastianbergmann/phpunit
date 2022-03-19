--TEST--
phpunit --testdox --colors=always --verbose -c tests/basic/configuration.basic.xml
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = realpath(__DIR__ . '/../../basic/configuration.basic.xml');
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = '--verbose';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
_files/raw_output_StatusTest.txt
