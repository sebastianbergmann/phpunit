--TEST--
The API for running examples of properties one at a time raises an exception, rather than running an example, when a previously registered error handler turns an issue that was triggered while the test suite was built into an exception
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

// the issue is triggered again when an example of the property is run again
foreach ([[0], [1]] as $choices) {
    try {
        $phpunit->replay($id, $choices, 1);
    } catch (PHPUnit\Exception $e) {
        print $e::class . ': ' . $e->getMessage() . PHP_EOL;
    }
}
--EXPECTF--
PHPUnit\Framework\PropertyBased\InvalidPropertyException: Property PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredWhileGeneratorIsDerivedTest::testParameterIsOfClassThatTriggersADeprecationWhenItIsLoaded cannot be checked: an error handler turned an issue that was triggered while the test suite was built into ErrorException at %sClassThatTriggersADeprecationWhenItIsLoaded.php:%d: deprecation triggered when the class is loaded
PHPUnit\Framework\PropertyBased\InvalidPropertyException: Property PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredWhileGeneratorIsDerivedTest::testParameterIsOfClassThatTriggersADeprecationWhenItIsLoaded cannot be checked: an error handler turned an issue that was triggered while the test suite was built into ErrorException at %sClassThatTriggersADeprecationWhenItIsLoaded.php:%d: deprecation triggered when the class is loaded
