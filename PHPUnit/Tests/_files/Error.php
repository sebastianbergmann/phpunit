<?php
class Error extends PHPUnit_Framework_TestCase
{
    public function runTest()
    {
        throw new Exception;
    }
}
?>
