--TEST--
Support --coverage-crap4j option.
--FILE--
<?php
require_once __DIR__ . '/../bootstrap.php';

$root = \org\bovigo\vfs\vfsStream::setup('coverage');
$coveragePath = \org\bovigo\vfs\vfsStream::path('coverage');
$configPath = __DIR__ . '/_files/coverage.xml';

$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = $configPath;
$_SERVER['argv'][] = '--coverage-crap4j';
$_SERVER['argv'][] = $coveragePath;
$_SERVER['argv'][] = __DIR__ . '/_files/Coverage.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)

Generating Crap4J report XML file ... done
