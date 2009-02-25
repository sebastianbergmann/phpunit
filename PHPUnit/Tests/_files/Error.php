<?php
class Error extends PHPUnit_Framework_TestCase
{
    protected function runTest($dependencyInput = NULL)
    {
        throw new Exception;
    }
}
?>
