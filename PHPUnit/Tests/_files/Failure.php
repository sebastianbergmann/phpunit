<?php
class Failure extends PHPUnit_Framework_TestCase
{
    public function runTest()
    {
        $this->fail();
    }
}
?>
