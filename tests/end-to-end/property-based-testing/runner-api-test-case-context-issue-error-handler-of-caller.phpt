--TEST--
The API for running examples of properties one at a time does not let an error handler of the caller turn an issue that was triggered while the test suite was built into an exception
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-test-case-context-issue');

set_error_handler(static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool {
    if (($errorNumber & error_reporting()) === 0) {
        return false;
    }

    throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
});

$phpunit = PropertyRunner::boot('phpunit.xml');
$id      = 'PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredWhileGeneratorIsDerivedTest::testParameterIsOfClassThatTriggersADeprecationWhenItIsLoaded';

foreach ([[0], [1]] as $choices) {
    print $phpunit->replay($id, $choices, 1)['outcome'] . PHP_EOL;
}
--EXPECT--
passed
passed
