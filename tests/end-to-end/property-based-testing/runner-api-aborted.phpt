--TEST--
The API for running examples of properties one at a time aborts an example that fails or errors without checking the property
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

$phpunit = PropertyRunner::boot('phpunit.xml', 'AbortedTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\AbortedTest::';

foreach (['testArgumentsCannotBeGenerated' => [3], 'testCannotBeSetUp' => [3], 'testRunsAnInvalidStateMachine' => []] as $method => $choices) {
    $result = $phpunit->replay($prefix . $method, $choices, 1);

    print $method . ': ' . $result['outcome'] . ' (' . $result['failure']['description'] . ': ' . $result['failure']['message'] . ')' . PHP_EOL;
}

try {
    $phpunit->replay($prefix . 'testHasAGeneratorMethodThatRaisesAnException', [3], 1);
} catch (PHPUnit\Exception $e) {
    print $e::class . ': ' . $e->getMessage() . PHP_EOL;
}
--EXPECT--
testArgumentsCannotBeGenerated: aborted (RuntimeException at tests/AbortedTest.php:31: cannot be generated)
testCannotBeSetUp: aborted (RuntimeException at tests/AbortedTest.php:47: cannot be set up)
testRunsAnInvalidStateMachine: aborted (PHPUnit\Framework\PropertyBased\InvalidStateMachineException at tests/AbortedTest.php:67: State machine PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\MachineWithoutRules cannot be run: it has no method with #[Rule])
PHPUnit\Framework\PropertyBased\InvalidPropertyException: Property PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\AbortedTest::testHasAGeneratorMethodThatRaisesAnException cannot be checked: creating its generators raised RuntimeException at tests/AbortedTest.php:41: no generator
