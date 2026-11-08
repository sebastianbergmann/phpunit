--TEST--
The API for running examples of properties one at a time removes the error handlers that an example registered and PHPUnit's own when the example ends the process, so that the error handler of the caller is active again
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

$errorHandler = static fn (): bool => false;

set_error_handler($errorHandler);

$errorReporting = error_reporting();

register_shutdown_function(static function () use ($errorHandler, $errorReporting): void
{
    $currentErrorHandler = set_error_handler(static fn (): bool => false);

    restore_error_handler();

    var_dump($currentErrorHandler === $errorHandler);
    var_dump(error_reporting() === $errorReporting);
});

$phpunit->replay('PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExitWithErrorHandlerTest::testRegistersErrorHandlersAndEndsTheProcess', [0], 1);
--EXPECT--
bool(true)
bool(true)
