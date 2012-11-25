<?php
class ThresholdMethodTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @threshold 0.0001
     */
    public function test()
    {
        usleep(2000);
    }
}
