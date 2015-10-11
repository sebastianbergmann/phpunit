--TEST--
#1868: Support --coverage-text option with default value.
--FILE--
<?php
require __DIR__ . '/../../../bootstrap.php';

$_SERVER['argv'][1] = '-c=' . __DIR__ . '/options/coverage.xml';
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/CoverageTest.php';
$_SERVER['argv'][3] = '--coverage-text';
$_SERVER['argv'][4] = '--colors=never';

/**
 * This use case is difficult to implement without introducing a BC break,
 * since the feature is currently being developed
 * @see https://github.com/symfony/symfony/pull/12773
 */

PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.

Time: %s ms, Memory: %sMb

OK (1 test, 1 assertion)

Code Coverage Report:   
  %s-%s-%s %s-%s-%s     
                        
 Summary:               
  Classes: 100.00% (1/1)
  Methods: 100.00% (1/1)
  Lines:   100.00% (2/2)

CoverageTest
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  2/  2)
