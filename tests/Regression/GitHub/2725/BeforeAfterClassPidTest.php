<?php

namespace Issue2725;

use PHPUnit\Framework\TestCase;

/**
 * @runClassInSeparateProcess
 */
class BeforeAfterClassPidTest extends TestCase
{
    const PID_VARIABLE = 'current_pid';

    /**
     * @beforeClass
     */
    public static function showPidBefore()
    {
        $GLOBALS[static::PID_VARIABLE] = getmypid();
    }

    public function testMethod1WithItsBeforeAndAfter()
    {
        $this->assertEquals($GLOBALS[static::PID_VARIABLE], getmypid());
    }

    public function testMethod2WithItsBeforeAndAfter()
    {
        $this->assertEquals($GLOBALS[static::PID_VARIABLE], getmypid());
    }

    /**
     * @afterClass
     */
    public static function showPidAfter()
    {
        if ($GLOBALS[static::PID_VARIABLE] - getmypid() !== 0) {
            echo "\n@afterClass output - PID difference should be zero!";
        }

        unset($GLOBALS[static::PID_VARIABLE]);
    }
}
