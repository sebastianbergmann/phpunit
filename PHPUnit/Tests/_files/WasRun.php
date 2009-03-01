<?php
class WasRun extends PHPUnit_Framework_TestCase
{
    public $wasRun = FALSE;

    protected function runTest()
    {
        $this->wasRun = TRUE;
    }
}
?>
