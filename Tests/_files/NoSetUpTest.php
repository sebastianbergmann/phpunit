<?php
class NoSetupTest extends PHPUnit_Framework_TestCase
{
    public $setup = FALSE;
    public $testSomething = FALSE;

    protected function setUp()
    {
        $this->setup = TRUE;
    }

    protected function tearDown()
    {
        $this->setup = TRUE;
    }

    /**
     * @noSetup
     */
    public function testSomething()
    {
        $this->testSomething = TRUE;
    }
}
