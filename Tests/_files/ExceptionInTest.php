<?php
class ExceptionInTest extends PHPUnit_Framework_TestCase
{
    public $setUp = FALSE;
    public $assertPreConditions = FALSE;
    public $assertPostConditions = FALSE;
    public $tearDown = FALSE;
    public $testSomething = FALSE;

    protected function setUp()
    {
        $this->setUp = TRUE;
    }

    protected function assertPreConditions()
    {
        $this->assertPreConditions = TRUE;
    }

    public function testSomething()
    {
        $this->testSomething = TRUE;
        throw new Exception;
    }

    protected function assertPostConditions()
    {
        $this->assertPostConditions = TRUE;
    }

    protected function tearDown()
    {
        $this->tearDown = TRUE;
    }
}
