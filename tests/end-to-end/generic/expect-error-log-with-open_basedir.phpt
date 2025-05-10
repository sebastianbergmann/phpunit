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
.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Expect Error Log (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLog)
 ✔ One

OK (1 test, 1 assertion)
