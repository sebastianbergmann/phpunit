--TEST--
The API for running examples of properties one at a time cannot be booted when PHPUnit triggers an error while it is booted, for a property that cannot be checked, for instance
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-invalid-property');

try {
    PropertyRunner::boot('phpunit.xml');
} catch (PHPUnit\Exception $e) {
    print $e::class . ': ' . $e->getMessage() . PHP_EOL;
}
--EXPECT--
PHPUnit\Runner\PropertyBased\ErrorTriggeredWhileBootingException: Booting PHPUnit triggered an error:

1) PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\InvalidPropertyTest::testUsesAGeneratorThatDoesNotExist
Property PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\InvalidPropertyTest::testUsesAGeneratorThatDoesNotExist cannot be checked: the generator for parameter $value is specified as PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\InvalidPropertyTest::doesNotExist(), which does not exist
