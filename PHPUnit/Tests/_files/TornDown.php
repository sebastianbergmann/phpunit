<?php
class TornDown extends PHPUnit_Framework_TestCase
{
    public $tornDown = FALSE;

    protected function tearDown()
    {
        $this->tornDown = TRUE;
    }

    protected function runTest()
    {
        throw new Exception;
    }
}
?>
