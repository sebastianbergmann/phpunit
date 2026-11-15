--TEST--
phpunit --parallel=2 prints what PHP displays for an error that is triggered in a worker outside of a test to its standard output, where PHP displays it in a sequential run
--FILE--
<?php declare(strict_types=1);
$process = proc_open(
    [
        PHP_BINARY,
        '-d',
        'display_errors=1',
        '-d',
        'log_errors=0',
        __DIR__ . '/../../../../phpunit',
        '--do-not-record-test-run-history',
        '--no-configuration',
        '--parallel=2',
        __DIR__ . '/_files/TriggersWarningBeforeFirstTestTest.php',
    ],
    [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes,
);

$standardOutput = stream_get_contents($pipes[1]);
fclose($pipes[1]);

$standardError = stream_get_contents($pipes[2]);
fclose($pipes[2]);

proc_close($process);

print "standard error:\n" . $standardError . "\n";
print "standard output:\n" . $standardOutput;
--EXPECTF--
standard error:

standard output:
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers


Warning: warning triggered before the first test in %sTriggersWarningBeforeFirstTestTest.php on line %d
.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
