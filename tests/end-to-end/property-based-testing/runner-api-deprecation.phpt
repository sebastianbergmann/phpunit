--TEST--
The API for running examples of properties one at a time verifies the expectations for deprecations of an example
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

print PropertyRunner::boot('phpunit.xml', 'DeprecationTest')->replay(
    'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\DeprecationTest::testExpectsADeprecation',
    [3],
    1,
)['outcome'] . PHP_EOL;
--EXPECT--
passed
