--TEST--
The API for running examples of properties one at a time restores the handler of the SIGALRM signal, the alarm, and whether signals are handled asynchronously of the caller after an example
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

$phpunit = PropertyRunner::boot('phpunit.xml', 'RunnerApiTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\RunnerApiTest::';
$alarms  = 0;
$handler = static function () use (&$alarms): void
{
    $alarms++;
};

$waitForAlarm = static function () use (&$alarms): void
{
    $deadline = hrtime(true) + 3_000_000_000;

    while ($alarms === 0 && hrtime(true) < $deadline) {
        usleep(10_000);
    }
};

pcntl_signal(SIGALRM, $handler);
pcntl_async_signals(false);
pcntl_alarm(30);

print $phpunit->replay($prefix . 'testPasses', [0], 1)['outcome'] . PHP_EOL;

$remaining = pcntl_alarm(0);

var_dump(pcntl_signal_get_handler(SIGALRM) === $handler);
var_dump(pcntl_async_signals());
var_dump($remaining >= 29 && $remaining <= 30);

// an alarm of the caller after the example calls the handler of the caller
pcntl_async_signals(true);
pcntl_alarm(1);

$waitForAlarm();

var_dump($alarms);

// an alarm of the caller that would have gone off while the example ran goes
// off after it
$alarms = 0;

pcntl_alarm(1);

print $phpunit->replay($prefix . 'testExceedsItsTimeLimit', [0], 1)['outcome'] . PHP_EOL;

var_dump($alarms);

$waitForAlarm();

var_dump($alarms);
--EXPECT--
passed
bool(true)
bool(false)
bool(true)
int(1)
timeout
int(0)
int(1)
