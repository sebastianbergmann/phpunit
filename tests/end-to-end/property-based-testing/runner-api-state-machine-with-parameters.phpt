--TEST--
The steps that the API for running examples of properties one at a time exports for an example that runs a state machine do not refer to the state machine with a variable, and do not assign a value of a pool to a variable, that is named like a parameter of the property, by which the argument of the example is exported
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-state-machine-with-parameters');

// the arguments 3 and 7, item(), take() of the item, and stop
$result = PropertyRunner::boot('phpunit.xml')->replay(
    'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\StateMachineOfPropertyWithParametersTest::testTakesValuesFromAPool',
    [0, 0, 1, 0, 0, 1, 1, 0, 0],
    1,
    true,
);

print $result['outcome'] . PHP_EOL;

foreach ($result['arguments'] as $parameter => $argument) {
    print '$' . $parameter . ' = ' . $argument . ';' . PHP_EOL;
}

print implode(PHP_EOL, $result['steps']) . PHP_EOL;
--EXPECT--
passed
$machine = 3;
$items1 = 7;
$items_1 = $stateMachine->item();
$stateMachine->take(item: $items_1);
