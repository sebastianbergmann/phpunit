--TEST--
The API for running examples of properties one at a time can be booted when PHPUnit triggers a warning while it is booted because of which the test run does not fail
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

$phpunit = PropertyRunner::boot('invalid-without-failing-on-phpunit-warning.xml', 'RunnerApiTest::testPasses');

foreach ($phpunit->properties() as $property) {
    print $property['id'] . PHP_EOL;
}

print $phpunit->replay('PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testPasses', [0], 1)['outcome'] . PHP_EOL;
--EXPECT--
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testPasses
passed
