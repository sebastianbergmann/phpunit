--TEST--
Don't run tests when wrong xdebug mode is set
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug')) {
    print 'skip: Extension xdebug must be loaded.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/coverage/coverage-no-tests-when-wrong-xdebug-mode.phpt');

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
