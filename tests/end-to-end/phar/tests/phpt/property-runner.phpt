--TEST--
The API for running the examples of properties one at a time can be used from the PHAR next to a copy of sebastian/choice-sequence whose classes are not prefixed
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
namespace SebastianBergmann\ChoiceSequence {
    // stands in for the copy of sebastian/choice-sequence that a tool which
    // uses the API has: the copy in the PHAR is prefixed, so the two do not
    // collide
    final class ChoiceSequence
    {
    }
}

namespace {
    use PHPUnit\Runner\PropertyBased\PropertyRunner;

    // the PHAR is included rather than executed, so it does not run tests;
    // when code coverage is collected, the PHAR was included already
    require_once __DIR__ . '/../../../../../build/artifacts/phpunit-snapshot.phar';

    chdir(__DIR__ . '/../../_files/property-runner');

    $runner = PropertyRunner::boot('phpunit.xml');
    $prefix = 'PHPUnit\TestFixture\Phar\PropertyRunner\ExampleTest::';

    foreach ($runner->properties() as $property) {
        print $property['id'] . PHP_EOL;
    }

    $failed = $runner->replay($prefix . 'testFailsForValuesGreaterThan100', [500], 1);

    print PHP_EOL . $failed['outcome'] . ': ' . $failed['failure']['description'] . PHP_EOL . PHP_EOL;

    $drawn = $runner->replay($prefix . 'testDrawsAValue', [1, 5, 0, 3], 1, true);

    var_export(
        [
            'outcome'     => $drawn['outcome'],
            'choices'     => $drawn['choices'],
            'kinds'       => array_column($drawn['spans'], 'kind'),
            'arguments'   => $drawn['arguments'],
            'drawnValues' => $drawn['drawnValues'],
        ],
    );

    print PHP_EOL . PHP_EOL;

    var_dump($runner->generate($prefix . 'testDrawsAValue', 1234, 1) === $runner->generate($prefix . 'testDrawsAValue', 1234, 1));
    var_dump(class_exists('PHPUnitPHAR\SebastianBergmann\ChoiceSequence\ChoiceSequence', false));
}
--EXPECTF--
PHPUnit\TestFixture\Phar\PropertyRunner\ExampleTest::testFailsForValuesGreaterThan100
PHPUnit\TestFixture\Phar\PropertyRunner\ExampleTest::testDrawsAValue

failed: PHPUnit\Framework\ExpectationFailedException at tests/ExampleTest.php:%d

array (
  'outcome' => 'passed',
  'choices' => 
  array (
    0 => 1,
    1 => 5,
    2 => 0,
    3 => 3,
  ),
  'kinds' => 
  array (
    0 => NULL,
    1 => 'element',
    2 => NULL,
    3 => 'element',
    4 => NULL,
  ),
  'arguments' => 
  array (
    'values' => '[5]',
  ),
  'drawnValues' => 
  array (
    0 => 
    array (
      'label' => 'digit',
      'value' => '3',
    ),
  ),
)

bool(true)
bool(true)
