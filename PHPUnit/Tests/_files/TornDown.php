<?php
class TornDown extends PHPUnit_Framework_TestCase
{
    public $tornDown = FALSE;

    protected function tearDown()
    {
        $this->tornDown = TRUE;
    }

    protected function runTest(array $dependencyInput = array())
    {
        throw new Exception;
    }
}
?>
