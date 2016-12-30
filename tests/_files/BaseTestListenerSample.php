<?php

use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\Test;

class BaseTestListenerSample extends BaseTestListener
{
    public $endCount = 0;

    public function endTest(Test $test, $time)
    {
        $this->endCount++;
    }
}
