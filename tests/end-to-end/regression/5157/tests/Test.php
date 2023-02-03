<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

final class Test extends TestCase
{
    private static bool $variable = false;

    public static function setUpBeforeClass(): void
    {
        self::$variable = true;
    }

    public function testOne(): void
    {
        $this->assertTrue(self::$variable);
    }
}
