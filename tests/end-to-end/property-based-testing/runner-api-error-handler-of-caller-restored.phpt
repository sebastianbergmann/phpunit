--TEST--
The API for running examples of properties one at a time registers the error handlers of the caller as before after an example, for the error levels that they were registered for, also when a generator did not remove an error handler that it registered, and when the example removed more error handlers than it registered
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-error-handler');

$issues       = [];
$errorHandler = static function (int $errorNumber, string $errorString) use (&$issues): bool
{
    $issues[] = $errorString;

    return true;
};

set_error_handler(static fn (): bool => true);
set_error_handler($errorHandler, E_USER_WARNING);

$phpunit = PropertyRunner::boot('phpunit.xml', 'ErrorHandlersOfExampleTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ErrorHandlersOfExampleTest::';

$triggerIssues = static function () use (&$issues, $errorHandler): void
{
    $activeErrorHandler = set_error_handler(null);

    restore_error_handler();

    var_dump($activeErrorHandler === $errorHandler);

    $issues = [];

    @trigger_error('notice', E_USER_NOTICE);
    trigger_error('warning', E_USER_WARNING);

    print 'issues that the error handler of the caller handled: ' . implode(', ', $issues) . PHP_EOL;
};

$triggerIssues();

foreach (['testGeneratorDoesNotRemoveTheErrorHandlerThatItRegistered', 'testGeneratorDoesNotRemoveTheErrorHandlerThatItRegistered'] as $method) {
    print $method . ': ' . $phpunit->replay($prefix . $method, [0], 1)['outcome'] . PHP_EOL;

    $triggerIssues();
}

// the error handler of the caller that was active is registered again, for
// all error levels, when the example removed it
print 'testRemovesMoreErrorHandlersThanItRegistered: ' . $phpunit->replay($prefix . 'testRemovesMoreErrorHandlersThanItRegistered', [0], 1)['outcome'] . PHP_EOL;

$triggerIssues();
--EXPECT--
bool(true)
issues that the error handler of the caller handled: warning
testGeneratorDoesNotRemoveTheErrorHandlerThatItRegistered: passed
bool(true)
issues that the error handler of the caller handled: warning
testGeneratorDoesNotRemoveTheErrorHandlerThatItRegistered: passed
bool(true)
issues that the error handler of the caller handled: warning
testRemovesMoreErrorHandlersThanItRegistered: passed
bool(true)
issues that the error handler of the caller handled: notice, warning
