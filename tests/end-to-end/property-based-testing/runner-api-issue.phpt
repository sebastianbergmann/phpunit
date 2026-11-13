--TEST--
The API for running examples of properties one at a time reports an example that triggers an issue for which the test run fails as one that falsifies the property
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

$phpunit = PropertyRunner::boot(__DIR__ . '/_files/runner-api-issue/phpunit.xml');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\IssueTest::';

foreach (['testTriggersAWarning', 'testTriggersANotice'] as $method) {
    $result = $phpunit->replay($prefix . $method, [0], 1);

    print $method . PHP_EOL;
    print '  outcome: ' . $result['outcome'] . PHP_EOL;

    if ($result['failure'] === null) {
        continue;
    }

    print '  kind: ' . $result['failure']['kind'] . PHP_EOL;
    print '  signature: ' . $result['failure']['signature'] . PHP_EOL;
    print '  class: ' . var_export($result['failure']['class'], true) . PHP_EOL;
    print '  location: ' . $result['failure']['location'] . PHP_EOL;
    print '  message: ' . $result['failure']['message'] . PHP_EOL;
}
--EXPECTF--
testTriggersAWarning
  outcome: failed
  kind: description
  signature: E_USER_WARNING at tests%eIssueTest.php:%d
  class: NULL
  location: tests%eIssueTest.php:%d
  message: E_USER_WARNING was triggered at tests%eIssueTest.php:%d: message
testTriggersANotice
  outcome: passed
