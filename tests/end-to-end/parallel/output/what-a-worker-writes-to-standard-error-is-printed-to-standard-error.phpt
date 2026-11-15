--TEST--
phpunit --parallel=2 prints what the tests of a worker write to STDERR to its standard error, and what they write to STDOUT to its standard output
--FILE--
<?php declare(strict_types=1);
$process = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../../../../phpunit',
        '--do-not-record-test-run-history',
        '--no-configuration',
        '--parallel=2',
        __DIR__ . '/_files/AWritesOutputTest.php',
        __DIR__ . '/_files/BWritesOutputTest.php',
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
A: written to STDERR by the second test
B: written to STDERR by the second test

standard output:
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

A: setUpBeforeClass()
A: written to STDOUT by the first test
..A: tearDownAfterClass()
B: setUpBeforeClass()
B: written to STDOUT by the first test
..                                                                4 / 4 (100%)B: tearDownAfterClass()


Time: %s, Memory: %s

OK (4 tests, 4 assertions)
