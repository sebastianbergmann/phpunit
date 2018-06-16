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
        if ($GLOBALS['PID_BEFORE'] != getmypid()) {
            echo "\n@afterClass output - PID difference should be zero\n";
            echo "PID_BEFORE: {$GLOBALS['PID_BEFORE']}\n";
            echo "mypid: " . getmypid() . "\n";
        }
    }
}
