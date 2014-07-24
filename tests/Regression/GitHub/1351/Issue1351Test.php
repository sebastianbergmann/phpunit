<?php
class Issue1351Test extends PHPUnit_Framework_TestCase
{
    protected $instance;

    /**
     * @runInSeparateProcess
     */
    public function testFailurePre()
    {
        $this->instance = new ChildProcessClass1351();
        $this->assertFalse(TRUE, 'Expected failure.');
    }

    public function testFailurePost()
    {
        $this->assertNull($this->instance);
        $this->assertFalse(class_exists('ChildProcessClass1351', false), 'ChildProcessClass1351 is not loaded.');
    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionPre()
    {
        $this->instance = new ChildProcessClass1351();
        try {
            throw new LogicException('Expected exception.');
        } catch (LogicException $e) {
            throw new RuntimeException('Expected rethrown exception.', 0, $e);
        }
    }

    public function testExceptionPost()
    {
        $this->assertNull($this->instance);
        $this->assertFalse(class_exists('ChildProcessClass1351', false), 'ChildProcessClass1351 is not loaded.');
    }
}
