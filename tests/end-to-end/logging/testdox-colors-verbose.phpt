--TEST--
phpunit --testdox --colors=always -c tests/basic/configuration.basic.xml
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/configuration.basic.xml';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF_EXTERNAL--
_files/raw_output_StatusTest.txt
