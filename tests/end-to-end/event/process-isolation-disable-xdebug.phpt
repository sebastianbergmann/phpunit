--TEST--
Subprocesses auto-disable xdebug when no debugger is attached.
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug')) {
    print 'skip: Extension xdebug must be loaded.';
} elseif (xdebug_is_debugger_active() === true) {
    print 'skip: Debugger must not be attached.';
}

--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/XdebugIsDisabled.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\XdebugIsDisabled, 1 test)
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Event\XdebugIsDisabled::testOne)
Test Prepared (PHPUnit\TestFixture\Event\XdebugIsDisabled::testOne)
Test Passed (PHPUnit\TestFixture\Event\XdebugIsDisabled::testOne)
Test Finished (PHPUnit\TestFixture\Event\XdebugIsDisabled::testOne)
Child Process Finished
Test Suite Finished (PHPUnit\TestFixture\Event\XdebugIsDisabled, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
