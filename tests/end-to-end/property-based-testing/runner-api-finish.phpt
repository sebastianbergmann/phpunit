--TEST--
The API for running examples of properties one at a time calls the hook methods of the test class that was prepared last that are called after its last test once when it is finished, and prepares the test class again for an example that is run afterwards
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-finish');

$phpunit = PropertyRunner::boot('phpunit.xml', 'FinishedTest|FailingSetUpBeforeClassWithTearDownAfterClassTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

$replay = static function (string $property) use ($phpunit, $prefix): void
{
    print $property . ': ' . $phpunit->replay($prefix . $property, [3], 1)['outcome'] . PHP_EOL;
};

$finish = static function () use ($phpunit): void
{
    print 'finish()' . PHP_EOL;

    $phpunit->finish();
};

print '-- no test class is prepared before the first example' . PHP_EOL;

$finish();

print '-- the test class that was prepared last is finished once' . PHP_EOL;

$replay('FinishedTest::testPasses');
$replay('FinishedTest::testPasses');
$finish();
$finish();

print '-- a test class that was finished is prepared again' . PHP_EOL;

$replay('FinishedTest::testPasses');
$finish();

print '-- a test class that was finished is not finished again before an example of another test class' . PHP_EOL;

$replay('OtherFinishedTest::testPasses');
$replay('FinishedTest::testPasses');
$finish();

print '-- a test class whose hook methods that are called before its first test did not succeed is prepared again' . PHP_EOL;

$replay('FailingSetUpBeforeClassWithTearDownAfterClassTest::testIsNeverRun');
$replay('FailingSetUpBeforeClassWithTearDownAfterClassTest::testIsNeverRun');
$finish();
$replay('FailingSetUpBeforeClassWithTearDownAfterClassTest::testIsNeverRun');
$finish();

print '-- a test class that is not finished is finished when the process ends' . PHP_EOL;

$replay('OtherFinishedTest::testPasses');
--EXPECT--
-- no test class is prepared before the first example
finish()
-- the test class that was prepared last is finished once
FinishedTest::setUpBeforeClass() was called, for preparation 1
FinishedTest::testPasses: passed
FinishedTest::testPasses: passed
finish()
FinishedTest::tearDownAfterClass() was called, for preparation 1
finish()
-- a test class that was finished is prepared again
FinishedTest::setUpBeforeClass() was called, for preparation 2
FinishedTest::testPasses: passed
finish()
FinishedTest::tearDownAfterClass() was called, for preparation 2
-- a test class that was finished is not finished again before an example of another test class
OtherFinishedTest::setUpBeforeClass() was called, for preparation 1
OtherFinishedTest::testPasses: passed
OtherFinishedTest::tearDownAfterClass() was called, for preparation 1
FinishedTest::setUpBeforeClass() was called, for preparation 3
FinishedTest::testPasses: passed
finish()
FinishedTest::tearDownAfterClass() was called, for preparation 3
-- a test class whose hook methods that are called before its first test did not succeed is prepared again
FailingSetUpBeforeClassWithTearDownAfterClassTest::setUpBeforeClass() was called
FailingSetUpBeforeClassWithTearDownAfterClassTest::testIsNeverRun: aborted
FailingSetUpBeforeClassWithTearDownAfterClassTest::testIsNeverRun: aborted
finish()
FailingSetUpBeforeClassWithTearDownAfterClassTest::setUpBeforeClass() was called
FailingSetUpBeforeClassWithTearDownAfterClassTest::testIsNeverRun: aborted
finish()
-- a test class that is not finished is finished when the process ends
OtherFinishedTest::setUpBeforeClass() was called, for preparation 2
OtherFinishedTest::testPasses: passed
OtherFinishedTest::tearDownAfterClass() was called, for preparation 2
