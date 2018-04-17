<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class BeforeClassAndAfterClassTest extends TestCase
{
    public static $beforeClassWasRun = 0;
    public static $afterClassWasRun  = 0;

    public static function resetProperties()
    {
        self::$beforeClassWasRun = 0;
        self::$afterClassWasRun  = 0;
    }

    /**
     * @beforeClass
     */
    public static function initialClassSetup()
    {
        self::$beforeClassWasRun++;
    }

    /**
     * @afterClass
     */
    public static function finalClassTeardown()
    {
        self::$afterClassWasRun++;
    }

    public function test1()
    {
    }

    public function test2()
    {
    }
}
