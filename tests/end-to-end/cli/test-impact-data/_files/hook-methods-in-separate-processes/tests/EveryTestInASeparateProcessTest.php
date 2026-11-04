<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData\HookMethodsInSeparateProcesses;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class EveryTestInASeparateProcessTest extends TestCase
{
    private static string $settings = '';

    public static function setUpBeforeClass(): void
    {
        self::$settings = Settings::load();
    }

    public static function tearDownAfterClass(): void
    {
        TemporaryFiles::remove();
    }

    public function testOne(): void
    {
        $this->assertSame('loaded', self::$settings);
    }

    public function testTwo(): void
    {
        $this->assertSame('loaded', self::$settings);
    }
}
