--TEST--
GH-2724: Missing initialization of setRunClassInSeparateProcess() for tests without data providers
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/2724/SeparateClassRunMethodInNewProcessTest.php';

require_once __DIR__ . '/../../bootstrap.php';

\file_put_contents(__DIR__ . '/2724/parent_process_id.txt', \getmypid());

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 3 assertions)
