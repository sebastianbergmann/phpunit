<?php

use PHPUnit\Framework\BaseTestListener;

class BaseTestListenerSample extends BaseTestListener
{
    public $endCount = 0;

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $this->endCount++;
    }
}
