<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\TestCase;

class BeforeAndAfterTest extends TestCase
{
    public static $beforeWasRun;

    public static $afterWasRun;

    public static function resetProperties(): void
    {
        self::$beforeWasRun = 0;
        self::$afterWasRun  = 0;
    }

    /**
     * @before
     */
    public function initialSetup(): void
    {
        self::$beforeWasRun++;
    }

    /**
     * @after
     */
    public function finalTeardown(): void
    {
        self::$afterWasRun++;
    }

    public function test1(): void
    {
    }

    public function test2(): void
    {
    }
}
