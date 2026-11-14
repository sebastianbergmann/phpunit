--TEST--
The API for running examples of properties one at a time ends the output buffering that a generator started without ending it, and discards what was buffered, so that the level of output buffering of the caller is the one from before the example
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-output-buffering');

$phpunit = PropertyRunner::boot('phpunit.xml');
$id      = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\OutputBufferingOfGeneratorTest::testGeneratorDoesNotEndTheOutputBufferingThatItStarted';

ob_start();

print 'buffered by the caller' . PHP_EOL;

$level = ob_get_level();

foreach ([[0], [1]] as $choices) {
    print $phpunit->replay($id, $choices, 1)['outcome'] . PHP_EOL;

    var_dump(ob_get_level() === $level);
}

$buffered = ob_get_clean();

print $buffered;
--EXPECT--
buffered by the caller
passed
bool(true)
passed
bool(true)
