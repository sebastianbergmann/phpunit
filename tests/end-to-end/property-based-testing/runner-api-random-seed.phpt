--TEST--
The API for running examples of properties one at a time seeds the random number generators before each example with the random seed that is passed while it is booted
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

$phpunit = PropertyRunner::boot('phpunit.xml', null, 1234);
$id      = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RandomNumberTest::testFailsWithARandomNumber';

var_dump($phpunit->randomSeed());

$first  = $phpunit->replay($id, [0], 1)['failure']['message'];
$second = $phpunit->replay($id, [7], 1)['failure']['message'];

mt_srand(RandomNumberGeneratorSeeder::derive(1234, $id));

var_dump($first === $second);
var_dump($first === (string) mt_rand());
--EXPECT--
int(1234)
bool(true)
bool(true)
