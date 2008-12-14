<?php
require_once 'PHPUnit/Runner/BaseTestRunner.php';

class MockRunner extends PHPUnit_Runner_BaseTestRunner
{
    public function testEnded($testName)
    {
    }

    public function testFailed($status, PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e)
    {
    }

    public function testStarted($testName)
    {
    }

    protected function runFailed($message)
    {
    }
}
?>
