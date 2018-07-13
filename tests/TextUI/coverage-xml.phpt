--TEST--
Support --coverage-xml option.
--FILE--
<?php
require_once __DIR__ . '/../bootstrap.php';

$root = \org\bovigo\vfs\vfsStream::setup('coverage');
$configPath = __DIR__ . '/_files/coverage.xml';

$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = $configPath;
$_SERVER['argv'][] = '--coverage-xml';
$_SERVER['argv'][] = $root->url() . '/coverage.xml';
$_SERVER['argv'][] = __DIR__ . '/_files/Coverage.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)

Generating code coverage report in PHPUnit XML format ... done
