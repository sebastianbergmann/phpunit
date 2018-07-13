--TEST--
Support --coverage-php option.
--FILE--
<?php
require_once __DIR__ . '/../bootstrap.php';

$root = \org\bovigo\vfs\vfsStream::setup('root');

$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = __DIR__ . '/_files/coverage.xml';
$_SERVER['argv'][] = '--coverage-php';
$_SERVER['argv'][] = $root->url() . '/coverage';
$_SERVER['argv'][] = __DIR__ . '/_files/Coverage.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)

Generating code coverage report in PHP format ... done
