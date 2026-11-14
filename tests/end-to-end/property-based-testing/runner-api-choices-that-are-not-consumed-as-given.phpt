--TEST--
The API for running examples of properties one at a time replaces a choice that is greater than its maximum with this maximum, consumes 0 after the last of the given choices, and ignores the given choices that are left
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

$phpunit = PropertyRunner::boot('phpunit.xml', 'RunnerApiTest::testPasses|RunnerApiTest::testDrawsAList');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::';

foreach (
    [
        ['testPasses', [1_000_000]],
        ['testPasses', []],
        ['testPasses', [3, 7, 9]],
        ['testDrawsAList', [1_000_000, 1_000_000, 1_000_000]],
    ] as [$method, $choices]
) {
    $result = $phpunit->replay($prefix . $method, $choices, 1);

    print $method . ': ' . json_encode($choices) . ' is consumed as ' . json_encode($result['choices']) . ' with the maxima ' . json_encode($result['maxima']) . PHP_EOL;
}
--EXPECT--
testPasses: [1000000] is consumed as [1000] with the maxima [1000]
testPasses: [] is consumed as [0] with the maxima [1000]
testPasses: [3,7,9] is consumed as [3] with the maxima [1000]
testDrawsAList: [1000000,1000000,1000000] is consumed as [1,9,1,0,0] with the maxima [1,9,1,9,1]
