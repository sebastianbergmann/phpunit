--TEST--
GH-1216: PHPUnit bootstrap must take globals vars even when the file is specified in command line
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/1216/phpunit1216.xml';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/1216/bootstrap1216.php';
$_SERVER['argv'][] = __DIR__ . '/1216/Issue1216Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'Issue1216Test::testConfigAvailableInBootstrap' started
Test 'Issue1216Test::testConfigAvailableInBootstrap' ended


Time: %s, Memory: %s

OK (1 test, 1 assertion)
