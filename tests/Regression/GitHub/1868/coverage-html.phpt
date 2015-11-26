--TEST--
#1868: Support --coverage-html option.
--FILE--
<?php
require __DIR__ . '/../../../bootstrap.php';

$root = \org\bovigo\vfs\vfsStream::setup('coverage');
$coveragePath = \org\bovigo\vfs\vfsStream::url('coverage');
$configPath = __DIR__ . '/options/coverage.xml';

$_SERVER['argv'][1] = '-c=' . $configPath;
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/CoverageTest.php';
$_SERVER['argv'][3] = '--coverage-html=' . $coveragePath;

PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.

Time: %s ms, Memory: %sMb

OK (1 test, 1 assertion)

Generating code coverage report in HTML format ... done
