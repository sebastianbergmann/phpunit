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

final class DataProviderSkippedTest extends TestCase
{
    public static function providerMethod()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
        ];
    }

    public static function skippedTestProviderMethod(): array
    {
        self::markTestSkipped('skipped');
    }

    #[DataProvider('skippedTestProviderMethod')]
    public function testSkipped($a, $b, $c): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('providerMethod')]
    public function testAdd($a, $b, $c): void
    {
        $this->assertEquals($c, $a + $b);
    }
}
