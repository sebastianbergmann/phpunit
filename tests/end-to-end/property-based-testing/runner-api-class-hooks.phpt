--TEST--
The API for running examples of properties one at a time calls the hook methods of a test class that are called before its first test before the first example, and the ones that are called after its last test when the process ends
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api');

$phpunit = PropertyRunner::boot('phpunit.xml', 'ClassLevelHooksTest|FailingSetUpBeforeClassTest|SkippedInSetUpBeforeClassTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

foreach (['ClassLevelHooksTest::testIsPrepared', 'ClassLevelHooksTest::testIsPrepared', 'FailingSetUpBeforeClassTest::testIsNeverRun', 'FailingSetUpBeforeClassTest::testIsNeverRun', 'SkippedInSetUpBeforeClassTest::testIsNeverRun'] as $property) {
    $result = $phpunit->replay($prefix . $property, [3], 1);

    print $property . ': ' . $result['outcome'];

    if ($result['failure'] !== null) {
        print ' (' . $result['failure']['description'] . ': ' . $result['failure']['message'] . ')';
    }

    print PHP_EOL;
}
--EXPECT--
setUpBeforeClass() was called
ClassLevelHooksTest::testIsPrepared: passed
ClassLevelHooksTest::testIsPrepared: passed
FailingSetUpBeforeClassTest::testIsNeverRun: errored (RuntimeException at tests/FailingSetUpBeforeClassTest.php:20: cannot prepare the test class)
FailingSetUpBeforeClassTest::testIsNeverRun: errored (RuntimeException at tests/FailingSetUpBeforeClassTest.php:20: cannot prepare the test class)
SkippedInSetUpBeforeClassTest::testIsNeverRun: skipped
tearDownAfterClass() was called
