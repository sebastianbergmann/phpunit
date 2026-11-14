--TEST--
The API for running examples of properties one at a time can be booted again when booting it failed because the configuration file cannot be found or read
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

foreach (['tests', 'phpunit.xm'] as $configurationFile) {
    try {
        PropertyRunner::boot($configurationFile);
    } catch (PHPUnit\Exception $e) {
        print $e::class . PHP_EOL;
    }
}

$phpunit = PropertyRunner::boot('phpunit.xml', 'RunnerApiTest::testPasses');

foreach ($phpunit->properties() as $property) {
    print $property['id'] . PHP_EOL;
}

print $phpunit->replay('PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testPasses', [0], 1)['outcome'] . PHP_EOL;

try {
    PropertyRunner::boot('phpunit.xml');
} catch (PHPUnit\Exception $e) {
    print $e::class . PHP_EOL;
}
--EXPECT--
PHPUnit\Runner\PropertyBased\ConfigurationFileNotFoundException
PHPUnit\TextUI\XmlConfiguration\Exception
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testPasses
passed
PHPUnit\Runner\PropertyBased\AlreadyBootedException
