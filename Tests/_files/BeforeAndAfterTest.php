<?php
class BeforeAndAfterTest extends PHPUnit_Framework_TestCase
{
    public $beforeWasRun;
    public $afterWasRun;

    /**
     * @before
     */
    public function initialSetup()
    {
        $this->beforeWasRun = TRUE;
    }

    /**
     * @after
     */
    public function finalTeardown()
    {
        $this->afterWasRun = TRUE;
    }

    public function testEmpty() {}
}
