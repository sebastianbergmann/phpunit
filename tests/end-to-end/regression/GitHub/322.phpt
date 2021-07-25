--TEST--
GH-322: group commandline option should override group/exclude setting in phpunit.xml
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/322/phpunit322.xml';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'one';
$_SERVER['argv'][] = __DIR__ . '/322/Issue322Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'Issue322Test::testOne' started
Test 'Issue322Test::testOne' ended


Time: %s, Memory: %s

OK (1 test, 1 assertion)

