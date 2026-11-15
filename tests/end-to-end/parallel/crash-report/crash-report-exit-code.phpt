--TEST--
phpunit --parallel=2 gives an extension access to the exit code of a worker process that ended unexpectedly
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/../../extension-xml/_files/child-process-exit-status/src/autoload.php';
$_SERVER['argv'][] = '--extension';
$_SERVER['argv'][] = 'PHPUnit\TestFixture\Event\ChildProcessExitStatus\Extension';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/CrashingTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/SteadyTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECT--
child process that errored ended with exit code 2
child process that errored ended with exit code 2
child process that finished ended with exit code 0
