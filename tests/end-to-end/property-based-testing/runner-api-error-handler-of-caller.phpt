--TEST--
The API for running examples of properties one at a time hides the error handlers of the caller while code of the project is run, so that an example ends as in a test run, whatever the error handler of the caller does with the issues that it triggers
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
$errorHandler = static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine) use (&$issues): bool
{
    $issues[] = $errorString;

    if (($errorNumber & error_reporting()) === 0) {
        return false;
    }

    throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
};

set_error_handler($errorHandler);

$phpunit = PropertyRunner::boot('phpunit.xml', 'TriggersIssuesTest');

// the hook methods of the test class that are called after its last test are
// called when the process ends
register_shutdown_function(static function () use (&$issues): void
{
    print 'issues that the error handler of the caller handled: ' . implode(', ', $issues) . PHP_EOL;
});

foreach ($phpunit->properties() as $property) {
    print $property['method'] . ': ' . $phpunit->replay($property['id'], [0], 1)['outcome'] . PHP_EOL;

    $activeErrorHandler = set_error_handler(null);

    restore_error_handler();

    var_dump($activeErrorHandler === $errorHandler);
}

try {
    trigger_error('triggered by the caller', E_USER_WARNING);
} catch (ErrorException $e) {
    print 'the error handler of the caller raised an exception' . PHP_EOL;
}
--EXPECT--
testTriggersAWarning: passed
bool(true)
testTriggersADeprecation: passed
bool(true)
testTriggersASuppressedWarning: passed
bool(true)
testTriggersAWarningWhileItsArgumentsAreGenerated: passed
bool(true)
the error handler of the caller raised an exception
issues that the error handler of the caller handled: triggered by the caller
