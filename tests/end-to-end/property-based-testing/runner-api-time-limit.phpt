--TEST--
The API for running examples of properties one at a time enforces the time limit of an example
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

var_export(
    PropertyRunner::boot('phpunit.xml')->replay(
        'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testExceedsItsTimeLimit',
        [0],
        1,
    )['failure'],
);
--EXPECT--
array (
  'kind' => 'timeout',
  'signature' => 'timeout',
  'class' => NULL,
  'location' => NULL,
  'description' => 'exceeding the time limit',
  'message' => 'Execution aborted after 1 second',
)
