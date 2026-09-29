<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData\OutsideOfTests;

use PHPUnit\Framework\TestCase;

final class BeforeClassTest extends TestCase
{
    private static string $settings = '';

    public static function setUpBeforeClass(): void
    {
        self::$settings = Settings::load();
    }

    public function testReadsWhatWasSetUpBeforeTheFirstTest(): void
    {
        $this->assertSame('loaded', self::$settings);
    }
}
