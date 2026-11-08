--TEST--
The API for running examples of properties one at a time creates the generators of a property again when its test class is prepared again, as they can depend on what setUpBeforeClass() set up
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-prepared-again');

$phpunit = PropertyRunner::boot('phpunit.xml');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

foreach (
    [
        'GeneratorFromSetUpBeforeClassTest::testIdentifierWasSetUpWhenTheTestClassWasPrepared',
        'OtherTest::testPasses',
        'GeneratorFromSetUpBeforeClassTest::testIdentifierWasSetUpWhenTheTestClassWasPrepared',
    ] as $property
) {
    $result = $phpunit->replay($prefix . $property, [0], 1, true);

    print $property . ': ' . $result['outcome'];

    if ($result['arguments'] !== null) {
        print ' (' . implode(', ', $result['arguments']) . ')';
    }

    print PHP_EOL;
}
--EXPECT--
GeneratorFromSetUpBeforeClassTest::testIdentifierWasSetUpWhenTheTestClassWasPrepared: passed ('identifier of preparation 1')
OtherTest::testPasses: passed (0)
GeneratorFromSetUpBeforeClassTest::testIdentifierWasSetUpWhenTheTestClassWasPrepared: passed ('identifier of preparation 2')
