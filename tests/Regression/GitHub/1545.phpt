--TEST--
GH-1545: Output buffering broken by PHPUnit
--FILE--
<?php

require __DIR__ . '/../../bootstrap.php';

class Issue1545Test extends PHPUnit_Framework_TestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }
}

$suite = new PHPUnit_Framework_TestSuite();
$suite->addTestSuite('Issue1545Test');

ob_start();
echo 'start';
$suite->run();
$results = ob_get_contents();
ob_end_clean();

echo $results;

?>
--EXPECT--
start
