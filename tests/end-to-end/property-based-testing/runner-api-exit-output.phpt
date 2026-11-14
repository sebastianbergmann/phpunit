--TEST--
The API for running examples of properties one at a time discards the output of an example that ends the process
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-exit');

$phpunit = PropertyRunner::boot('phpunit.xml');

register_shutdown_function(static function (): void
{
    print 'shutdown function of the caller' . PHP_EOL;
});

print 'before the example' . PHP_EOL;

$phpunit->replay('PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExitWithOutputTest::testPrintsAndEndsTheProcess', [0], 1);
--EXPECT--
before the example
shutdown function of the caller
