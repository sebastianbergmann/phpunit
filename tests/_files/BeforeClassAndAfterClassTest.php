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

    public static function resetProperties(): void
    {
        self::$beforeClassWasRun = 0;
        self::$afterClassWasRun  = 0;
    }

    /**
     * @beforeClass
     */
    public static function initialClassSetup(): void
    {
        self::$beforeClassWasRun++;
    }

    /**
     * @afterClass
     */
    public static function finalClassTeardown(): void
    {
        self::$afterClassWasRun++;
    }

    public function test1(): void
    {
    }

    public function test2(): void
    {
    }
}
