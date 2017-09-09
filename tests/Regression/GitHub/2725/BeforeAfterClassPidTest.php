<?php
use PHPUnit\Framework\TestCase;

/**
 * @runClassInSeparateProcess
 */
class Issue2591_SeparateClassPreserveTest extends TestCase
{
    /**
     * @beforeClass
     */
    public static function showPidBefore()
    {
        $GLOBALS['PID_BEFORE'] = getmypid();
    }

    public function testComparePids()
    {
        $this->assertEquals($GLOBALS['PID_BEFORE'], getmypid());
    }

    public function testThatClassDidNotReload()
    {
        $this->assertEquals($GLOBALS['PID_BEFORE'], getmypid());
    }

    /**
     * @afterClass
     */
    public static function showPidAfter()
    {
        echo "\n@afterClass output - PID difference should be zero: " . ($GLOBALS['PID_BEFORE'] - getmypid());
    }

}
