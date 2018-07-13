--TEST--
Support --coverage-text with specified file
--FILE--
<?php
require_once __DIR__ . '/../bootstrap.php';

\org\bovigo\vfs\vfsStream::enableDotfiles();
$root = \org\bovigo\vfs\vfsStream::setup('root');

$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/coverage.xml';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = $root->path() . '/coverage.txt';
$_SERVER['argv'][] = __DIR__ . '/_files/Coverage.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s ms, Memory: %sMB

OK (1 test, 1 assertion)


Code Coverage Report:%s
  %s
%s
 Summary:%s
  Classes:  0.00% (0/1)
  Methods:  0.00% (0/1)
  Lines:    0.00% (0/2)
