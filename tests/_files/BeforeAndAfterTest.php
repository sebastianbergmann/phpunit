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

class BeforeAndAfterTest extends TestCase
{
    public static $beforeWasRun;
    public static $afterWasRun;

    public static function resetProperties()
    {
        self::$beforeWasRun = 0;
        self::$afterWasRun  = 0;
    }

    /**
     * @before
     */
    public function initialSetup()
    {
        self::$beforeWasRun++;
    }

    /**
     * @after
     */
    public function finalTeardown()
    {
        self::$afterWasRun++;
    }

    public function test1()
    {
    }

    public function test2()
    {
    }
}
