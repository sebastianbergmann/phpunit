--TEST--
The API for running examples of properties one at a time lists the properties of a test suite and runs their examples from choices or from a seed
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api');

$phpunit = PropertyRunner::boot('phpunit.xml', 'RunnerApiTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::';

foreach ($phpunit->properties() as $property) {
    print $property['id'] . PHP_EOL;
}

print PHP_EOL;

var_export($phpunit->replay($prefix . 'testFailsForValuesGreaterThan100', [500], 1));

print PHP_EOL . PHP_EOL;

foreach (['testPasses' => [0], 'testErrors' => [7], 'testIsRejected' => [0], 'testIsSkipped' => [0], 'testIsIncomplete' => [0]] as $method => $choices) {
    $result = $phpunit->replay($prefix . $method, $choices, 1);

    print $method . ': ' . $result['outcome'];

    if ($result['failure'] !== null) {
        print ' (' . $result['failure']['description'] . ': ' . $result['failure']['message'] . ')';
    }

    print PHP_EOL;
}

print PHP_EOL;

var_export($phpunit->replay($prefix . 'testDrawsAList', [1, 5, 1, 7, 0], 1)['spans']);

print PHP_EOL . PHP_EOL;

$generated = $phpunit->generate($prefix . 'testDrawsAList', 1234, 1);

var_dump($generated === $phpunit->generate($prefix . 'testDrawsAList', 1234, 1));
var_dump($generated === $phpunit->replay($prefix . 'testDrawsAList', $generated['choices'], 1));

print PHP_EOL;

foreach (
    [
        static fn () => $phpunit->replay($prefix . 'testIsNotAProperty', [], 1),
        static fn () => $phpunit->replay($prefix . 'testPasses', [-1], 1),
        static fn () => $phpunit->generate($prefix . 'testPasses', 1, 0),
        static fn () => PropertyRunner::boot('phpunit.xml'),
    ] as $call
) {
    try {
        $call();
    } catch (PHPUnit\Exception $e) {
        print $e::class . ': ' . $e->getMessage() . PHP_EOL;
    }
}
--EXPECTF--
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testPasses
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testFailsForValuesGreaterThan100
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testErrors
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testIsRejected
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testExceedsItsTimeLimit
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testDrawsAList
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testIsSkipped
PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testIsIncomplete

array (
  'outcome' => 'failed',
  'failure' => 
  array (
    'kind' => 'throwable',
    'signature' => 'PHPUnit\\Framework\\ExpectationFailedException at tests/RunnerApiTest.php:%d',
    'class' => 'PHPUnit\\Framework\\ExpectationFailedException',
    'location' => 'tests/RunnerApiTest.php:%d',
    'description' => 'PHPUnit\\Framework\\ExpectationFailedException at tests/RunnerApiTest.php:%d',
    'message' => 'Failed asserting that 500 is equal to 100 or is less than 100.',
  ),
  'choices' => 
  array (
    0 => 500,
  ),
  'spans' => 
  array (
    0 => 
    array (
      'start' => 0,
      'end' => 1,
      'label' => '%s',
      'parent' => NULL,
      'kind' => NULL,
    ),
  ),
  'maxima' => 
  array (
    0 => 1000,
  ),
)

testPasses: passed
testErrors: errored (RuntimeException at tests/RunnerApiTest.php:%d: error for 7)
testIsRejected: rejected
testIsSkipped: skipped
testIsIncomplete: incomplete

array (
  0 => 
  array (
    'start' => 0,
    'end' => 5,
    'label' => '%s',
    'parent' => NULL,
    'kind' => NULL,
  ),
  1 => 
  array (
    'start' => 0,
    'end' => 2,
    'label' => '%s',
    'parent' => 0,
    'kind' => 'element',
  ),
  2 => 
  array (
    'start' => 1,
    'end' => 2,
    'label' => '%s',
    'parent' => 1,
    'kind' => NULL,
  ),
  3 => 
  array (
    'start' => 2,
    'end' => 4,
    'label' => '%s',
    'parent' => 0,
    'kind' => 'element',
  ),
  4 => 
  array (
    'start' => 3,
    'end' => 4,
    'label' => '%s',
    'parent' => 3,
    'kind' => NULL,
  ),
  5 => 
  array (
    'start' => 4,
    'end' => 5,
    'label' => '%s',
    'parent' => 0,
    'kind' => 'element',
  ),
)

bool(true)
bool(true)

PHPUnit\Runner\PropertyBased\PropertyDoesNotExistException: Property "PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::testIsNotAProperty" does not exist
PHPUnit\Runner\PropertyBased\InvalidArgumentException: A choice is not a non-negative integer
PHPUnit\Runner\PropertyBased\InvalidArgumentException: The time limit must be at least one second, 0 seconds given
PHPUnit\Runner\PropertyBased\AlreadyBootedException: PHPUnit can only be booted once per process
