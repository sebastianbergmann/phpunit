--TEST--
Testdox: print error message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--display-errors';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-text';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  __DIR__ . '/../../_files/stop-on-fail-on/ErrorTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
Error (PHPUnit\TestFixture\TestRunnerStopping\Error)
 [ ] One
 [x] Two
