--TEST--
Testdox: print risky message
--FILE--
<?php declare(strict_types=1);

$parentDirectory = dirname(__DIR__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-text';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  realpath($parentDirectory. '/_files/stop-on-fail-on/RiskyTest.php');

require realpath($parentDirectory . '/../bootstrap.php');

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
Risky (PHPUnit\TestFixture\TestRunnerStopping\Risky)
 [x] One
 [x] Two
