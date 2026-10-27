--TEST--
The temporary files used for collecting code coverage are created in a directory with an unpredictable name that only the current user can access
--FILE--
<?php declare(strict_types=1);
use PHPUnit\TestFixture\PhptCodeCoverage\Foo;

$directory = dirname($GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST'][0]);

var_dump(dirname($directory) === realpath(sys_get_temp_dir()));
var_dump(preg_match('/^phpunit-phpt-[0-9a-f]{32}$/', basename($directory)));
var_dump(PHP_OS_FAMILY === 'Windows' || (fileperms($directory) & 0o077) === 0);
var_dump((new Foo)->doSomething());
--EXPECT--
bool(true)
int(1)
bool(true)
bool(true)
