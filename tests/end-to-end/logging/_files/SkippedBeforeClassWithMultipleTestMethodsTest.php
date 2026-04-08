<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Logging;

use PHPUnit\Framework\TestCase;

final class SkippedBeforeClassWithMultipleTestMethodsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        self::markTestSkipped('Skip all');
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    public function testThree(): void
    {
        $this->assertTrue(true);
    }
}
