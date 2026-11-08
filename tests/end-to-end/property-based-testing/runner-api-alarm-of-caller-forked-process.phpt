--TEST--
The API for running examples of properties one at a time restores the alarm of the caller only in the process in which the example started, not in a process that the example forked
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
$pid     = getmypid();
$alarms  = 0;

pcntl_signal(SIGALRM, static function () use ($pid, &$alarms): void
{
    $alarms++;

    if (getmypid() === $pid) {
        print 'alarm of the caller in the process that booted PHPUnit' . PHP_EOL;
    } else {
        print 'alarm of the caller in a forked process' . PHP_EOL;
    }
});

pcntl_async_signals(true);

// the forked process outlives the alarm of the caller
register_shutdown_function(static function () use ($pid): void
{
    if (getmypid() !== $pid) {
        usleep(3_000_000);
    }
});

pcntl_alarm(2);

print $phpunit->replay('PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ClassLevelHooksTest::testForksAProcessThatEnds', [3], 5)['outcome'] . PHP_EOL;

$deadline = hrtime(true) + 3_000_000_000;

while ($alarms === 0 && hrtime(true) < $deadline) {
    usleep(10_000);
}
--EXPECT--
ClassLevelHooksTest::setUpBeforeClass() was called
passed
alarm of the caller in the process that booted PHPUnit
ClassLevelHooksTest::tearDownAfterClass() was called
