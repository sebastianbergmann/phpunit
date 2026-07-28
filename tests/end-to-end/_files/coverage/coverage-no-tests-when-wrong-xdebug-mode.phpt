--TEST--
Don't run tests when wrong xdebug mode is set
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug')) {
    print 'skip: Extension xdebug must be loaded.';
}
--ENV--
XDEBUG_MODE=debug
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-coverage-wrong-xdebug-mode';

@mkdir($cacheDirectory, 0777, true);

$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--coverage-html';
$_SERVER['argv'][] = 'my_coverage_folder';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

There was 1 PHPUnit test runner warning:

1) XDEBUG_MODE=coverage (environment variable) or xdebug.mode=coverage (PHP configuration setting) has to be set

No tests executed!

