--TEST--
The API for running examples of properties one at a time cannot be cloned
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

$phpunit = PropertyRunner::boot('phpunit.xml', 'RunnerApiTest::testPasses');

try {
    clone $phpunit;
} catch (Error $e) {
    print $e::class . PHP_EOL;
}
--EXPECT--
Error
