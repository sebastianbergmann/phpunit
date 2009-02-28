<?php
require_once 'TornDown.php';

class TornDown2 extends TornDown
{
    protected function tearDown()
    {
        parent::tearDown();
        throw new Exception;
    }

    protected function runTest()
    {
        throw new Exception;
    }
}
?>
