--TEST--
The API for running examples of properties one at a time keeps the error handlers of the caller hidden while an example runs, and registers the one that was active as before afterwards, when a hook method that is called before the first test of its test class removed more error handlers than it registered
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

$phpunit = PropertyRunner::boot('phpunit.xml', 'BeforeFirstTestTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

// the error handler of the caller is only registered for all error levels
// when the hook method removed it
foreach (['RemovesAnErrorHandlerBeforeFirstTestTest', 'RemovesErrorHandlersBeforeFirstTestTest'] as $testClass) {
    print $testClass . ': ' . $phpunit->replay($prefix . $testClass . '::testTriggersAWarning', [0], 1)['outcome'] . PHP_EOL;
    print 'number of issues that the error handler of the caller handled meanwhile: ' . count($issues) . PHP_EOL;

    $activeErrorHandler = set_error_handler(null);

    restore_error_handler();

    var_dump($activeErrorHandler === $errorHandler);

    @trigger_error('notice', E_USER_NOTICE);
    trigger_error('warning', E_USER_WARNING);

    print 'issues that the error handler of the caller handled afterwards: ' . implode(', ', $issues) . PHP_EOL;

    $issues = [];
}
--EXPECT--
RemovesAnErrorHandlerBeforeFirstTestTest: passed
number of issues that the error handler of the caller handled meanwhile: 0
bool(true)
issues that the error handler of the caller handled afterwards: warning
RemovesErrorHandlersBeforeFirstTestTest: passed
number of issues that the error handler of the caller handled meanwhile: 0
bool(true)
issues that the error handler of the caller handled afterwards: notice, warning
