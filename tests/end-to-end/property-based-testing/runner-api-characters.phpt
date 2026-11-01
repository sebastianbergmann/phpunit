--TEST--
The API for running examples of properties one at a time exports the span of a string of Unicode characters as a span of the kind "characters"
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

chdir(__DIR__ . '/_files/runner-api');

$result = PropertyRunner::boot('phpunit.xml', 'StringTest')->replay(
    'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\StringTest::testDrawsAString',
    [1, CodePoints::index(0x61), 1, CodePoints::index(0x20AC), 0],
    1,
    true,
);

print $result['outcome'] . ': ' . $result['arguments']['value'] . PHP_EOL;

foreach ($result['spans'] as $span) {
    printf('[%d, %d) %s' . PHP_EOL, $span['start'], $span['end'], var_export($span['kind'], true));
}
--EXPECT--
passed: 'a€'
[0, 5) 'characters'
[0, 2) 'element'
[2, 4) 'element'
[4, 5) 'element'
