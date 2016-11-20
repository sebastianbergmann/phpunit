<?php
class Issue2317Test extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Exception
     * @expectedExceptionCode LOCALLY_DEFINED_CONSTANT
     */
    public function testWorks()
    {
        define('LOCALLY_DEFINED_CONSTANT', 1);
        throw new Exception('Message', LOCALLY_DEFINED_CONSTANT);
    }
}
