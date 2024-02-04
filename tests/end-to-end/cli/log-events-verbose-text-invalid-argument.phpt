--TEST--
Test fail with invalid path
--FILE--
<?php declare(strict_types=1);
$traceFile = sys_get_temp_dir() . '/invalid-directory/invalid.file';

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-verbose-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../_files/log-events-text';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

The path /tmp/invalid-directory/invalid.file specified for the --log-events-verbose-text option could not be resolved
