--TEST--
The API for running examples of properties one at a time only lists the properties that match the filter
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

foreach (PropertyRunner::boot('phpunit.xml', 'Errors|Rejected')->properties() as $property) {
    print $property['id'] . PHP_EOL;
}
--EXPECT--
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testErrors
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testIsRejected
