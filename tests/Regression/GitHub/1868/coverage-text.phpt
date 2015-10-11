--TEST--
#1868: Support --coverage-text with specified file
--FILE--
<?php
require __DIR__ . '/../../../bootstrap.php';

\org\bovigo\vfs\vfsStream::enableDotfiles();
$root = \org\bovigo\vfs\vfsStream::setup('coverage', null, ['coverage.log' => '']);
$coveragePath = $root->url() . '/coverage.log';

$_SERVER['argv'][1] = '-c=' . __DIR__ . '/options/coverage.xml';
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/CoverageTest.php';
$_SERVER['argv'][3] = '--coverage-text=' . $coveragePath;

PHPUnit_TextUI_Command::main(false);

$content = file_get_contents($coveragePath);
$lines = explode(PHP_EOL, $content);
array_walk($lines, 'trim');
// forced to expect with var_dump, since there are whitespace in the content
var_dump($lines);
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.

Time: %s ms, Memory: %sMb

OK (1 test, 1 assertion)
array(13) {
  [0]=>
  string(0) ""
  [1]=>
  string(0) ""
  [2]=>
  string(24) "Code Coverage Report:   "
  [3]=>
  string(24) "  %s   "
  [4]=>
  string(24) "                        "
  [5]=>
  string(24) " Summary:               "
  [6]=>
  string(24) "  Classes: 100.00% (1/1)"
  [7]=>
  string(24) "  Methods: 100.00% (1/1)"
  [8]=>
  string(24) "  Lines:   100.00% (2/2)"
  [9]=>
  string(0) ""
  [10]=>
  string(12) "CoverageTest"
  [11]=>
  string(53) "  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  2/  2)"
  [12]=>
  string(0) ""
}
