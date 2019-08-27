--TEST--
phpunit --colors=never --coverage-text=php://stdout ../../_files/phpt-for-coverage.phpt --whitelist ../../_files/CoveredClass.php
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug')) {
    print 'skip: Extension xdebug is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--bootstrap';
$_SERVER['argv'][3] = __DIR__ . '/../bootstrap.php';
$_SERVER['argv'][4] = '--colors=never';
$_SERVER['argv'][5] = '--coverage-text=php://stdout';
$_SERVER['argv'][6] = __DIR__ . '/../_files/phpt-for-multiwhitelist-coverage.phpt';
$_SERVER['argv'][7] = '--whitelist';
$_SERVER['argv'][8] = __DIR__ . '/../_files/CoveredClass.php';
$_SERVER['argv'][9] = '--whitelist';
$_SERVER['argv'][10] = __DIR__ . '/../_files/SampleClass.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)


Code Coverage Report:%w
%s
%w
 Summary:%w
  Classes: 100.00% (3/3)%w
  Methods: 100.00% (%d/%d)%w
  Lines:   100.00% (%d/%d)

CoveredClass
  Methods: 100.00% ( %d/ %d)   Lines: 100.00% (  %d/  %d)
CoveredParentClass
  Methods: 100.00% ( %d/ %d)   Lines: 100.00% (  %d/  %d)
SampleClass
  Methods: 100.00% ( %d/ %d)   Lines: 100.00% (  %d/  %d)
