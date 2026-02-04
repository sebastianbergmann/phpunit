<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\SkipInBeforeClass;

use PHPUnit\Framework\TestCase;

class SkippedBeforeClassTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        self::markTestSkipped('Skipped in before class');
    }

    public function test1(): void
    {
        self::assertTrue(true);
    }

    public function test2(): void
    {
        self::assertTrue(true);
    }
}
