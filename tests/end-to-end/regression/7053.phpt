--TEST--
GH-7053: Extensions loaded using "php -d extension=..." are loaded in child processes
--SKIPIF--
<?php declare(strict_types=1);
if ((require __DIR__ . '/7053/extension-that-is-not-loaded.php') === null) {
    print 'skip: This test requires an extension that is not loaded but can be loaded';
}
--FILE--
<?php declare(strict_types=1);
$extension = require __DIR__ . '/7053/extension-that-is-not-loaded.php';

$process = proc_open(
    [
        PHP_BINARY,
        '-d',
        'extension=' . $extension,
        __DIR__ . '/../../../phpunit',
        '--do-not-cache-result',
        '--no-configuration',
        __DIR__ . '/7053/Issue7053Test.php',
    ],
    [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes,
);

print stream_get_contents($pipes[1]);
fclose($pipes[1]);

$stderr = stream_get_contents($pipes[2]);
fclose($pipes[2]);

proc_close($process);

if ($stderr !== '') {
    print $stderr;
}
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
