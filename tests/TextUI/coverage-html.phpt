--TEST--
Support --coverage-html option.
--FILE--
<?php
require_once __DIR__ . '/../bootstrap.php';

$root = \org\bovigo\vfs\vfsStream::setup('coverage');
$coveragePath = $root->url();

$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/coverage.xml';
$_SERVER['argv'][] = '--coverage-html';
$_SERVER['argv'][] = $coveragePath;
$_SERVER['argv'][] = __DIR__ . '/_files/Coverage.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)

Generating code coverage report in HTML format ... done
