--TEST--
phpunit --no-output --log-events-verbose-text logfile.txt
--FILE--
<?php declare(strict_types=1);
$traceFile = 'dasdasdasdasd/dasdasdsa.log';

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-verbose-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../_files/log-events-text';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit 10.5.3 by Sebastian Bergmann and contributors.

Specified path: dasdasdasdasd/dasdasdsa.log can't be resolved
