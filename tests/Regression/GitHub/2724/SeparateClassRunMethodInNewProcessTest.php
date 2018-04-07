<?php

/**
 * @runClassInSeparateProcess
 */
class SeparateClassRunMethodInNewProcessTest extends PHPUnit\Framework\TestCase
{
    const PROCESS_ID_FILE_PATH = __DIR__ . '/parent_process_id.txt';
    const INITIAL_MASTER_PID = 0;
    const INITIAL_PID1 = 1;

    public static $masterPid = self::INITIAL_MASTER_PID;
    public static $pid1 = self::INITIAL_PID1;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (file_exists(self::PROCESS_ID_FILE_PATH)) {
            static::$masterPid = (int) file_get_contents(self::PROCESS_ID_FILE_PATH);
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        if (file_exists(self::PROCESS_ID_FILE_PATH)) {
            unlink(self::PROCESS_ID_FILE_PATH);
        }
    }

    public function testMethodShouldGetDifferentPidThanMaster()
    {
        static::$pid1 = getmypid();

        $this->assertNotEquals(self::INITIAL_PID1, static::$pid1);
        $this->assertNotEquals(self::INITIAL_MASTER_PID, static::$masterPid);

        $this->assertNotEquals(static::$pid1, static::$masterPid);
    }
}
