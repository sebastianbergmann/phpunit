<?php
require_once 'PHPUnit/Extensions/PerformanceTestCase.php';

class SleepTest extends PHPUnit_Extensions_PerformanceTestCase
{
    public function testSleepTwoSeconds()
    {
        sleep(2);
    }
}
?>
