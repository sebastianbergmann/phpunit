--TEST--
phpunit --testdox --colors=always --verbose -c tests/basic/configuration.basic.xml
--XFAIL--
TestDox logging has not been migrated to events yet.
See https://github.com/sebastianbergmann/phpunit/issues/4702 for details.
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/configuration.basic.xml';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = '--verbose';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF_EXTERNAL--
_files/raw_output_StatusTest.txt
