--TEST--
The API for running examples of properties one at a time tells the random seed that PHPUnit chooses when the configuration file configures a random execution order
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;
use PHPUnit\Runner\RandomNumberGeneratorSeeder;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-random-seed');

$phpunit    = PropertyRunner::boot('random-order.xml');
$id         = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RandomNumberTest::testFailsWithARandomNumber';
$randomSeed = $phpunit->randomSeed();

var_dump(is_int($randomSeed));

$message = $phpunit->replay($id, [0], 1)['failure']['message'];

mt_srand(RandomNumberGeneratorSeeder::derive($randomSeed, $id));

var_dump($message === (string) mt_rand());
--EXPECT--
bool(true)
bool(true)
