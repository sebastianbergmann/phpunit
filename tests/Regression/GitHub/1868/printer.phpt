--TEST--
#1868: Support --printer option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/CoverageTest.php';
$_SERVER['argv'][3] = '--printer=TestPrinter';

require __DIR__ . '/../../../bootstrap.php';

class TestPrinter extends \PHPUnit_TextUI_ResultPrinter
{
    public function printResult(PHPUnit_Framework_TestResult $result)
    {
        $this->write('test');
        parent::printResult($result);
    }
}

PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.test

Time: %s, Memory: %sMb

OK (1 test, 1 assertion)
