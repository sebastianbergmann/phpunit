--TEST--
The API for running examples of properties one at a time enforces the time limit of an example, which covers generating its arguments and running it, and aborts an example whose arguments take longer to generate than its time limit allows
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api');

$phpunit = PropertyRunner::boot('phpunit.xml');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

var_export($phpunit->replay($prefix . 'RunnerApiTest::testExceedsItsTimeLimit', [0], 1)['failure']);

print PHP_EOL . PHP_EOL;

foreach (['SlowGenerationTest::testArgumentsTakeTooLongToBeGenerated' => 1, 'SlowGenerationTest::testArgumentsAndExampleTakeTooLongTogether' => 2] as $property => $timeLimit) {
    $result = $phpunit->replay($prefix . $property, [3], $timeLimit);

    print $property . ': ' . $result['outcome'] . ' (' . $result['failure']['kind'] . ': ' . $result['failure']['message'] . ')' . PHP_EOL;
}
--EXPECT--
array (
  'kind' => 'timeout',
  'signature' => 'timeout',
  'class' => NULL,
  'location' => NULL,
  'description' => 'exceeding the time limit',
  'message' => 'Execution aborted after 1 second',
)

SlowGenerationTest::testArgumentsTakeTooLongToBeGenerated: aborted (timeout: Execution aborted after 1 second)
SlowGenerationTest::testArgumentsAndExampleTakeTooLongTogether: timeout (timeout: Execution aborted after 1 second)
