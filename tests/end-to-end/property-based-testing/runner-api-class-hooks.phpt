--TEST--
The API for running examples of properties one at a time prepares one test class at a time: it calls the hook methods of a test class that are called before its first test before its first example, and the ones that are called after its last test before an example of another test class or when the process ends
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

$phpunit = PropertyRunner::boot('phpunit.xml', 'ClassLevelHooksTest|FailingSetUpBeforeClassTest|SkippedInSetUpBeforeClassTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

foreach (
    [
        'ClassLevelHooksTest::testIsPrepared',
        'ClassLevelHooksTest::testIsPrepared',
        'ClassLevelHooksTest::testForksAProcessThatEnds',
        'OtherClassLevelHooksTest::testIsPrepared',
        'ClassLevelHooksTest::testIsPrepared',
        'FailingSetUpBeforeClassTest::testIsNeverRun',
        'FailingSetUpBeforeClassTest::testIsNeverRun',
        'SkippedInSetUpBeforeClassTest::testIsNeverRun',
        'ClassLevelHooksTest::testIsPrepared',
    ] as $property
) {
    $result = $phpunit->replay($prefix . $property, [3], 1);

    print $property . ': ' . $result['outcome'];

    if ($result['failure'] !== null) {
        print ' (' . $result['failure']['description'] . ': ' . $result['failure']['message'] . ')';
    }

    print PHP_EOL;
}
--EXPECT--
ClassLevelHooksTest::setUpBeforeClass() was called
ClassLevelHooksTest::testIsPrepared: passed
ClassLevelHooksTest::testIsPrepared: passed
ClassLevelHooksTest::testForksAProcessThatEnds: passed
ClassLevelHooksTest::tearDownAfterClass() was called
OtherClassLevelHooksTest::setUpBeforeClass() was called
OtherClassLevelHooksTest::testIsPrepared: passed
OtherClassLevelHooksTest::tearDownAfterClass() was called
ClassLevelHooksTest::setUpBeforeClass() was called
ClassLevelHooksTest::testIsPrepared: passed
ClassLevelHooksTest::tearDownAfterClass() was called
FailingSetUpBeforeClassTest::testIsNeverRun: aborted (RuntimeException at tests/FailingSetUpBeforeClassTest.php:20: cannot prepare the test class)
FailingSetUpBeforeClassTest::testIsNeverRun: aborted (RuntimeException at tests/FailingSetUpBeforeClassTest.php:20: cannot prepare the test class)
SkippedInSetUpBeforeClassTest::testIsNeverRun: skipped
ClassLevelHooksTest::setUpBeforeClass() was called
ClassLevelHooksTest::testIsPrepared: passed
ClassLevelHooksTest::tearDownAfterClass() was called
