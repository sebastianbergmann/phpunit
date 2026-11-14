--TEST--
The API for running examples of properties one at a time exports the spans of a value that a step of a state machine produces for a pool, and of the positions of the values that steps take from the pool, as spans of the kinds "referable", "reference", and "consumingreference"
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-state-machine');

// item(), inspect() of the item, take() of the item, and stop
$result = PropertyRunner::boot('phpunit.xml')->replay(
    'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\StateMachineWithPoolTest::testTakesValuesFromAPool',
    [1, 0, 0, 1, 1, 0, 1, 2, 0, 0],
    1,
    true,
);

print $result['outcome'] . PHP_EOL;
print implode(PHP_EOL, $result['steps']) . PHP_EOL;

foreach ($result['spans'] as $span) {
    if ($span['kind'] !== null) {
        printf('[%d, %d) %s' . PHP_EOL, $span['start'], $span['end'], $span['kind']);
    }
}
--EXPECT--
passed
$items1 = $machine->item();
$machine->inspect(item: $items1);
$machine->take(item: $items1);
[2, 3) referable
[5, 6) reference
[8, 9) consumingreference
