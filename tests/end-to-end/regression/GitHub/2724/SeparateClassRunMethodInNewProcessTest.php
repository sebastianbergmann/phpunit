<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @runClassInSeparateProcess
 */
class SeparateClassRunMethodInNewProcessTest extends PHPUnit\Framework\TestCase
{
    const PROCESS_ID_FILE_PATH = __DIR__ . '/parent_process_id.txt';

    const INITIAL_MASTER_PID   = 0;

    const INITIAL_PID1         = 1;

    public static $masterPid = self::INITIAL_MASTER_PID;

    public static $pid1      = self::INITIAL_PID1;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (\file_exists(self::PROCESS_ID_FILE_PATH)) {
            static::$masterPid = (int) \file_get_contents(self::PROCESS_ID_FILE_PATH);
        }
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (\file_exists(self::PROCESS_ID_FILE_PATH)) {
            \unlink(self::PROCESS_ID_FILE_PATH);
        }
    }

    public function testMethodShouldGetDifferentPidThanMaster(): void
    {
        static::$pid1 = \getmypid();

        $this->assertNotEquals(self::INITIAL_PID1, static::$pid1);
        $this->assertNotEquals(self::INITIAL_MASTER_PID, static::$masterPid);

        $this->assertNotEquals(static::$pid1, static::$masterPid);
    }
}
