--TEST--
The process exits with exit code 73 when an output path is outside the directory specified with --restrict-file-output
--FILE--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-exit-code';

mkdir($allowed);

$process = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../../../../phpunit',
        '--do-not-record-test-run-history',
        '--no-configuration',
        '--restrict-file-output',
        $allowed,
        '--log-junit',
        sys_get_temp_dir() . '/junit.xml',
        __DIR__ . '/../../_files/restrict-file-output/ExampleTest.php',
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
Exit code: 73
--CLEAN--
<?php declare(strict_types=1);
rmdir(sys_get_temp_dir() . '/phpunit-restrict-file-output-exit-code');
