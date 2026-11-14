--TEST--
The API for running examples of properties one at a time registers the error handlers of the caller as before, ends the output buffering that was started, and leaves the handler of the SIGALRM signal of the caller as it is when a hook method that is called after the last test of a test class raises an exception, and no longer registers the error handler that this hook method removed for the examples that follow
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\AfterLastTestMethodFailedException;
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-finish');

$issues       = [];
$errorHandler = static function (int $errorNumber, string $errorString) use (&$issues): bool
{
    $issues[] = $errorString;

    return true;
};

$signalHandler = static function (): void
{
};

set_error_handler(static fn (): bool => true);
set_error_handler($errorHandler, E_USER_WARNING);

pcntl_signal(SIGALRM, $signalHandler);
pcntl_async_signals(false);

$phpunit = PropertyRunner::boot('phpunit.xml', 'StateOfCallerChangedInFailingTearDownAfterClassTest|TriggersANoticeTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

$outputBufferingLevel = ob_get_level();

$stateOfCaller = static function () use (&$issues, $errorHandler, $signalHandler, $outputBufferingLevel): void
{
    $activeErrorHandler = set_error_handler(null);

    restore_error_handler();

    $issues = [];

    @trigger_error('notice', E_USER_NOTICE);
    trigger_error('warning', E_USER_WARNING);

    print 'error handler of the caller is active: ';
    var_dump($activeErrorHandler === $errorHandler);
    print 'issues that the error handler of the caller handled: ' . implode(', ', $issues) . PHP_EOL;
    print 'level of output buffering is the one from before: ';
    var_dump(ob_get_level() === $outputBufferingLevel);
    print 'handler of the SIGALRM signal of the caller is registered: ';
    var_dump(pcntl_signal_get_handler(SIGALRM) === $signalHandler);
    print 'signals are handled asynchronously: ';
    var_dump(pcntl_async_signals());
};

$replay = static function (string $property) use ($phpunit, $prefix, $stateOfCaller): void
{
    try {
        $result = $phpunit->replay($prefix . $property, [3], 1);

        print $property . ': ' . $result['outcome'];

        if ($result['failure'] !== null) {
            print ' (' . $result['failure']['class'] . ': ' . $result['failure']['message'] . ')';
        }

        print PHP_EOL;
    } catch (AfterLastTestMethodFailedException $e) {
        print $property . ': ' . $e::class . PHP_EOL;

        $stateOfCaller();
    }
};

print '-- finish()' . PHP_EOL;

$replay('StateOfCallerChangedInFailingTearDownAfterClassTest::testTriggersANotice');

try {
    $phpunit->finish();
} catch (AfterLastTestMethodFailedException $e) {
    print 'finish(): ' . $e::class . PHP_EOL;

    $stateOfCaller();
}

$replay('TriggersANoticeTest::testTriggersANotice');

print '-- an example of a property of another test class' . PHP_EOL;

$replay('StateOfCallerChangedInFailingTearDownAfterClassTest::testTriggersANotice');
$replay('TriggersANoticeTest::testTriggersANotice');
$replay('TriggersANoticeTest::testTriggersANotice');

$stateOfCaller();
--EXPECT--
-- finish()
StateOfCallerChangedInFailingTearDownAfterClassTest::testTriggersANotice: errored (ErrorException: notice)
finish(): PHPUnit\Runner\PropertyBased\AfterLastTestMethodFailedException
error handler of the caller is active: bool(true)
issues that the error handler of the caller handled: warning
level of output buffering is the one from before: bool(true)
handler of the SIGALRM signal of the caller is registered: bool(true)
signals are handled asynchronously: bool(false)
TriggersANoticeTest::testTriggersANotice: passed
-- an example of a property of another test class
StateOfCallerChangedInFailingTearDownAfterClassTest::testTriggersANotice: errored (ErrorException: notice)
TriggersANoticeTest::testTriggersANotice: PHPUnit\Runner\PropertyBased\AfterLastTestMethodFailedException
error handler of the caller is active: bool(true)
issues that the error handler of the caller handled: warning
level of output buffering is the one from before: bool(true)
handler of the SIGALRM signal of the caller is registered: bool(true)
signals are handled asynchronously: bool(false)
TriggersANoticeTest::testTriggersANotice: passed
error handler of the caller is active: bool(true)
issues that the error handler of the caller handled: warning
level of output buffering is the one from before: bool(true)
handler of the SIGALRM signal of the caller is registered: bool(true)
signals are handled asynchronously: bool(false)
