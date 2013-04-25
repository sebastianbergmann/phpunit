<?php
class BeforeClassAndAfterClassTest extends PHPUnit_Framework_TestCase
{
    public static $beforeClassWasRun = 0;
    public static $afterClassWasRun = 0;

    /**
     * @beforeClass
     */
    public function initialSetup()
    {
        self::$beforeClassWasRun++;
    }

    /**
     * @afterClass
     */
    public static function finalTeardown()
    {
        self::$afterClassWasRun++;
    }

    public function test1() {}
    public function test2() {}
}
