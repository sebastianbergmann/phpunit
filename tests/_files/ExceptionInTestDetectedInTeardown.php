<?php
use PHPUnit\Framework\TestCase;

use PHPUnit\Runner\BaseTestRunner;

class ExceptionInTestDetectedInTeardown extends TestCase
{
    public $exceptionDetected = false;
    
    public function testSomething()
    {
        throw new Exception;
    }

    protected function tearDown(): void
    {
        if (BaseTestRunner::STATUS_ERROR == $this->getStatus()) {
            $this->exceptionDetected = true;
        }
    }
}
