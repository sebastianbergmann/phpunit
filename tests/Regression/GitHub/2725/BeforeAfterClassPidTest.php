<?php

namespace Issue2725;

use PHPUnit\Framework\TestCase;

/**
 * @runClassInSeparateProcess
 */
class BeforeAfterClassPidTest extends TestCase
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
