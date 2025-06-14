--TEST--
Don't run tests when coverage driver is not loaded
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('xdebug') || extension_loaded('pcov')) {
    print 'skip: No debug driver should be loaded.';
}
--ENV--
XDEBUG_MODE=debug
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--coverage-html';
$_SERVER['argv'][] = 'my_coverage_folder';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

There was 1 PHPUnit test runner warning:

1) No code coverage driver available

No tests executed!

