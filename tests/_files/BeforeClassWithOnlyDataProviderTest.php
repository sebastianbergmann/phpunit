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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BeforeClassWithOnlyDataProviderTest extends TestCase
{
    public static $setUpBeforeClassWasCalled;
    public static $beforeClassWasCalled;

    public static function resetProperties(): void
    {
        self::$setUpBeforeClassWasCalled = false;
        self::$beforeClassWasCalled      = false;
    }

    /**
     * @beforeClass
     */
    public static function someAnnotatedSetupMethod(): void
    {
        self::$beforeClassWasCalled = true;
    }

    public static function dummyProvider(): array
    {
        return [[1]];
    }

    public static function setUpBeforeClass(): void
    {
        self::$setUpBeforeClassWasCalled = true;
    }

    #[DataProvider('dummyProvider')]
    public function testDummy(): void
    {
        $this->assertFalse(false);
    }
}
