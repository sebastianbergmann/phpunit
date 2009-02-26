<?php
class WasRun extends PHPUnit_Framework_TestCase
{
    public $wasRun = FALSE;

    protected function runTest(array $dependencyInput = array())
    {
        $this->wasRun = TRUE;
    }
}
?>
