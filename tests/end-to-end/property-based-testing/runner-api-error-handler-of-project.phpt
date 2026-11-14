--TEST--
The API for running examples of properties one at a time registers the error handlers that the bootstrap script and the hook methods of a test class that are called before its first test registered while an example runs, as in a test run, and not for the caller
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-error-handler-of-project');

$activeErrorHandler = static function (): ?callable
{
    $activeErrorHandler = set_error_handler(null);

    restore_error_handler();

    return $activeErrorHandler;
};

$phpunit = PropertyRunner::boot('phpunit.xml');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

var_dump($activeErrorHandler());

foreach (
    [
        'ErrorHandlerOfBootstrapScriptTest::testTriggersAWarning',
        'ErrorHandlerOfBootstrapScriptTest::testTriggersANotice',
        'ErrorHandlerOfTestClassTest::testTriggersANotice',
        'ErrorHandlerOfTestClassTest::testTriggersANotice',
        'ErrorHandlerOfBootstrapScriptTest::testTriggersANotice',
        'ErrorHandlerOfBootstrapScriptTest::testTriggersAWarning',
    ] as $property
) {
    $result = $phpunit->replay($prefix . $property, [0], 1);

    print $property . ': ' . $result['outcome'];

    if ($result['failure'] !== null) {
        print ' (' . $result['failure']['class'] . ': ' . $result['failure']['message'] . ')';
    }

    print PHP_EOL;

    var_dump($activeErrorHandler());
}
--EXPECT--
NULL
ErrorHandlerOfBootstrapScriptTest::testTriggersAWarning: errored (ErrorException: warning)
NULL
ErrorHandlerOfBootstrapScriptTest::testTriggersANotice: passed
NULL
ErrorHandlerOfTestClassTest::testTriggersANotice: errored (ErrorException: notice)
NULL
ErrorHandlerOfTestClassTest::testTriggersANotice: errored (ErrorException: notice)
NULL
ErrorHandlerOfBootstrapScriptTest::testTriggersANotice: passed
NULL
ErrorHandlerOfBootstrapScriptTest::testTriggersAWarning: errored (ErrorException: warning)
NULL
