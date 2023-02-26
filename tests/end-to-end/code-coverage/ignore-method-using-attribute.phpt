--TEST--
Methods can be ignored for code coverage using an attribute
--INI--
pcov.directory=tests/end-to-end/code-coverage/ignore-method-using-attribute/src/
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('pcov')) {
    return;
}

if (!extension_loaded('xdebug')) {
    print 'skip: This test requires a code coverage driver';
}

if (version_compare(phpversion('xdebug'), '3.1', '>=') && in_array('coverage', xdebug_info('mode'), true)) {
    return;
}

$mode = getenv('XDEBUG_MODE');

if ($mode === false || $mode === '') {
    $mode = ini_get('xdebug.mode');
}

if ($mode === false ||
    !in_array('coverage', explode(',', $mode), true)) {
    print 'skip: XDEBUG_MODE=coverage or xdebug.mode=coverage has to be set';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__ . '/ignore-method-using-attribute/src';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = __DIR__ . '/ignore-method-using-attribute/tests';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s MB

OK (1 test, 1 assertion)


Code Coverage Report:   
  %s   
                        
 Summary:               
  Classes: 100.00% (1/1)
  Methods: 100.00% (1/1)
  Lines:   100.00% (1/1)

PHPUnit\TestFixture\IgnoreMethodUsingAttribute\CoveredClass
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
