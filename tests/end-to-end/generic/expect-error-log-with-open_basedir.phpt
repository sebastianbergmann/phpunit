--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6197
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/ExpectErrorLogTest.php';

ini_set('open_basedir', (ini_get('open_basedir') ? ini_get('open_basedir') . PATH_SEPARATOR : '') . dirname(__DIR__, 3));

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

logged a side effect
I                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Expect Error Log (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLog)
 ∅ One
   │
   │ Could not create writable error_log file.

   │

OK, but there were issues!
Tests: 1, Assertions: 1, Incomplete: 1.
