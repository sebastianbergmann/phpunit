--TEST--
phpunit --testdox --colors=never -c tests/basic/configuration.basic.xml --filter Success
--XFAIL--
TestDox logging has not been migrated to events yet.
See https://github.com/sebastianbergmann/phpunit/issues/4702 for details.
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/configuration.basic.xml';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'Success';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

Test result status with and without message
 ✔ Success
 ✔ Success with message

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
