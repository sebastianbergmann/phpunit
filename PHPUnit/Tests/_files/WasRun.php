<?php
class WasRun extends PHPUnit_Framework_TestCase
{
    public $wasRun = FALSE;

    protected function runTest($dependencyInput = NULL)
    {
        $this->wasRun = TRUE;
    }
}
?>
