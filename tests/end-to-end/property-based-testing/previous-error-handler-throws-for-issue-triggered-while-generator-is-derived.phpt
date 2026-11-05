--TEST--
A previously registered error handler that turns an issue triggered while the generator of a property is derived into an exception errors the property instead of aborting the test runner
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/_files/IssueTriggeredWhileGeneratorIsDerivedTest.php';

require __DIR__ . '/../../bootstrap.php';

set_error_handler(static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool {
    if (($errorNumber & error_reporting()) === 0) {
        return false;
    }

    throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
});

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredWhileGeneratorIsDerivedTest::testParameterIsOfClassThatTriggersADeprecationWhenItIsLoaded
ErrorException: deprecation triggered when the class is loaded

%sClassThatTriggersADeprecationWhenItIsLoaded.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
