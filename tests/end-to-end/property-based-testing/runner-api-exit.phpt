--TEST--
The API for running examples of properties one at a time restores what it changed for an example that ends the process before its own shutdown function and the ones that are registered after it are called
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-exit');

$phpunit = PropertyRunner::boot('phpunit.xml');

$signalHandler = static function (): void
{
    print 'alarm of the caller' . PHP_EOL;
};

$errorHandler = static fn (): bool => false;

pcntl_signal(SIGALRM, $signalHandler);
pcntl_async_signals(false);
set_error_handler($errorHandler);

$errorReporting = error_reporting();

register_shutdown_function(static function () use ($signalHandler, $errorHandler, $errorReporting): void
{
    $currentErrorHandler = set_error_handler(static fn (): bool => false);

    restore_error_handler();

    var_dump(pcntl_signal_get_handler(SIGALRM) === $signalHandler);
    var_dump(pcntl_async_signals());
    var_dump($currentErrorHandler === $errorHandler);
    var_dump(error_reporting() === $errorReporting);
});

$phpunit->replay('PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExitTest::testEndsTheProcess', [0], 1);

print 'not reached' . PHP_EOL;
--EXPECT--
tearDownAfterClass() completed
bool(true)
bool(false)
bool(true)
bool(true)
