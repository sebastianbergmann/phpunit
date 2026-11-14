--TEST--
The API for running examples of properties one at a time cannot be booted with a configuration file or a filter that is an empty string, and can be booted afterwards
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

foreach ([['', null], ['phpunit.xml', '']] as [$configurationFile, $filter]) {
    try {
        PropertyRunner::boot($configurationFile, $filter);
    } catch (Throwable $t) {
        print $t::class . ': ' . $t->getMessage() . PHP_EOL;
    }
}

$phpunit = PropertyRunner::boot('phpunit.xml', 'RunnerApiTest::testPasses');

foreach ($phpunit->properties() as $property) {
    print $property['id'] . PHP_EOL;
}
--EXPECT--
PHPUnit\Runner\PropertyBased\InvalidArgumentException: The configuration file must not be an empty string
PHPUnit\Runner\PropertyBased\InvalidArgumentException: The filter must not be an empty string
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testPasses
