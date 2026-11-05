--TEST--
The API for running examples of properties one at a time cannot be booted with a directory that does not contain a configuration file
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

try {
    PropertyRunner::boot('tests');
} catch (PHPUnit\Exception $e) {
    print $e::class . ': ' . $e->getMessage() . PHP_EOL;
}
--EXPECT--
PHPUnit\Runner\PropertyBased\ConfigurationFileNotFoundException: No configuration file was found in directory "tests"
