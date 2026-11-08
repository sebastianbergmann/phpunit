--TEST--
The API for running examples of properties one at a time calls the hook methods of a test class that are called after its last test only in the process that prepared the test class, also in a process that is forked between examples
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

$phpunit = PropertyRunner::boot('phpunit.xml', 'ClassLevelHooksTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

$replay = static function (string $process, string $property) use ($phpunit, $prefix): void
{
    print $process . ': ' . $property . ': ' . $phpunit->replay($prefix . $property, [3], 1)['outcome'] . PHP_EOL;
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

print '-- a forked process that prepares a test class finishes it when it ends' . PHP_EOL;

$inForkedProcess(static fn () => $replay('forked', 'ClassLevelHooksTest::testIsPrepared'));

print '-- a forked process does not finish the test class that the process from which it was forked prepared' . PHP_EOL;

$replay('parent', 'ClassLevelHooksTest::testIsPrepared');

$inForkedProcess(static fn () => $replay('forked', 'OtherClassLevelHooksTest::testIsPrepared'));

$replay('parent', 'ClassLevelHooksTest::testIsPrepared');
--EXPECT--
-- a forked process that prepares a test class finishes it when it ends
ClassLevelHooksTest::setUpBeforeClass() was called
forked: ClassLevelHooksTest::testIsPrepared: passed
ClassLevelHooksTest::tearDownAfterClass() was called
-- a forked process does not finish the test class that the process from which it was forked prepared
ClassLevelHooksTest::setUpBeforeClass() was called
parent: ClassLevelHooksTest::testIsPrepared: passed
OtherClassLevelHooksTest::setUpBeforeClass() was called
forked: OtherClassLevelHooksTest::testIsPrepared: passed
OtherClassLevelHooksTest::tearDownAfterClass() was called
parent: ClassLevelHooksTest::testIsPrepared: passed
ClassLevelHooksTest::tearDownAfterClass() was called
