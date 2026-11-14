--TEST--
The API for running examples of properties one at a time exports the span of an element that the minimum size of a collection requires as a span of the kind "requiredelement"
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;
use SebastianBergmann\ChoiceSequence\CodePoints;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-required-elements');

$result = PropertyRunner::boot('phpunit.xml', 'NonEmptyStringTest')->replay(
    'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApiRequiredElements\NonEmptyStringTest::testDrawsANonEmptyString',
    [CodePoints::index(0x61), 1, CodePoints::index(0x20AC), 0],
    1,
    true,
);

print $result['outcome'] . ': ' . $result['arguments']['value'] . PHP_EOL;

foreach ($result['spans'] as $span) {
    printf('[%d, %d) %s' . PHP_EOL, $span['start'], $span['end'], var_export($span['kind'], true));
}
--EXPECT--
passed: 'a€'
[0, 4) 'characters'
[0, 1) 'requiredelement'
[1, 3) 'element'
[3, 4) 'element'
