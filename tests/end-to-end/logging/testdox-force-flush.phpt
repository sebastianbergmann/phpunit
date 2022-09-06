--TEST--
phpunit --testdox --colors=never -c tests/basic/configuration.basic.xml --filter Success
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-progress';
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

Time: %s, Memory: %s

Test result status with and without message
 ✔ Success
 ✔ Success with message

OK (2 tests, 2 assertions)
