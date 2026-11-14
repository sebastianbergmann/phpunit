--TEST--
The API for running examples of properties one at a time does not call the hook methods of a test class that are called after its last test when the test class is finished in a process that is forked from the one that prepared it
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

$phpunit = PropertyRunner::boot('phpunit.xml', 'FinishedTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

$replay = static function (string $process, string $property) use ($phpunit, $prefix): void
{
    print $process . ': ' . $property . ': ' . $phpunit->replay($prefix . $property, [3], 1)['outcome'] . PHP_EOL;
};

$finish = static function (string $process) use ($phpunit): void
{
    print $process . ': finish()' . PHP_EOL;

    $phpunit->finish();
};

$inForkedProcess = static function (Closure $closure): void
{
    $pid = pcntl_fork();

    if ($pid === 0) {
        $closure();

        exit(0);
    }

    pcntl_waitpid($pid, $status);
};

print '-- a forked process does not finish the test class that the process from which it was forked prepared' . PHP_EOL;

$replay('parent', 'FinishedTest::testPasses');

$inForkedProcess(static fn () => $finish('forked'));

$finish('parent');

print '-- a forked process finishes a test class that it prepared' . PHP_EOL;

$replay('parent', 'FinishedTest::testPasses');

$inForkedProcess(
    static function () use ($finish, $replay): void
    {
        $finish('forked');
        $replay('forked', 'FinishedTest::testPasses');
        $finish('forked');
    },
);

$finish('parent');
--EXPECT--
-- a forked process does not finish the test class that the process from which it was forked prepared
FinishedTest::setUpBeforeClass() was called, for preparation 1
parent: FinishedTest::testPasses: passed
forked: finish()
parent: finish()
FinishedTest::tearDownAfterClass() was called, for preparation 1
-- a forked process finishes a test class that it prepared
FinishedTest::setUpBeforeClass() was called, for preparation 2
parent: FinishedTest::testPasses: passed
forked: finish()
FinishedTest::setUpBeforeClass() was called, for preparation 3
forked: FinishedTest::testPasses: passed
forked: finish()
FinishedTest::tearDownAfterClass() was called, for preparation 3
parent: finish()
FinishedTest::tearDownAfterClass() was called, for preparation 2
