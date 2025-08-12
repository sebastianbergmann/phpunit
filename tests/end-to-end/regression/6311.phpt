--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6311
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../phpt/phpt-invalid-require.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) SKIPIF section triggered a fatal error: 
Warning: require(%afile.php): Failed to open stream: No such file or directory in Standard input code on line %d

Fatal error: Uncaught Error: Failed opening required%a
%A
