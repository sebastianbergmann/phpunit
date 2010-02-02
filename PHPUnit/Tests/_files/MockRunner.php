<?php
require_once 'PHPUnit/Runner/BaseTestRunner.php';

class MockRunner extends PHPUnit_Runner_BaseTestRunner
{
    protected function runFailed($message)
    {
    }
}
