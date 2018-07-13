--TEST--
Support --printer option.
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';
$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/Coverage.php';
$_SERVER['argv'][] = '--printer=TestPrinter';


use PHPUnit\TextUI\ResultPrinter;
use PHPUnit\Framework\TestResult;

class TestPrinter extends ResultPrinter
{
    public function printResult(TestResult $result): void
    {
        $this->write('test');
        parent::printResult($result);
    }
}

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)test

Time: %s ms, Memory: %s

OK (1 test, 1 assertion)
