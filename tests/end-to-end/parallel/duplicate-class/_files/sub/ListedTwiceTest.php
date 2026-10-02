<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelDuplicateClass;

use const FILE_APPEND;
use const LOCK_EX;
use function file_put_contents;
use function sys_get_temp_dir;
use PHPUnit\Framework\TestCase;

final class ListedTwiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        file_put_contents(
            sys_get_temp_dir() . '/phpunit-parallel-duplicate-class.marker',
            "setUpBeforeClass\n",
            FILE_APPEND | LOCK_EX,
        );
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
