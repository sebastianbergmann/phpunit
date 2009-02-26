<?php
class Failure extends PHPUnit_Framework_TestCase
{
    protected function runTest(array $dependencyInput = array())
    {
        $this->fail();
    }
}
?>
