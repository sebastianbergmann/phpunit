--TEST--
The API for running examples of properties one at a time has no random seed when neither a random seed is passed while it is booted nor a random execution order is configured, and rejects a random seed that is not positive
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-random-seed');

try {
    PropertyRunner::boot('phpunit.xml', null, 0);
} catch (PHPUnit\Exception $e) {
    print $e::class . ': ' . $e->getMessage() . PHP_EOL;
}

var_dump(PropertyRunner::boot('phpunit.xml')->randomSeed());
--EXPECT--
PHPUnit\Runner\PropertyBased\InvalidArgumentException: The random seed must be a positive integer, 0 given
NULL
