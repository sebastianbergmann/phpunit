--TEST--
Shutdown Handler: process exits with exit code 2
--FILE--
<?php declare(strict_types=1);

$process = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../../../phpunit',
        '--do-not-cache-result',
        '--no-configuration',
        '--filter',
        'testWithoutMessage',
        __DIR__ . '/../../_files/WithExitTest.php',
    ],
    [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes,
);

stream_get_contents($pipes[1]);
fclose($pipes[1]);

stream_get_contents($pipes[2]);
fclose($pipes[2]);

$exitCode = proc_close($process);

print 'Exit code: ' . $exitCode . PHP_EOL;
--EXPECT--
Exit code: 2
