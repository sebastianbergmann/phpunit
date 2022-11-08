<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DataProvider;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DataProviderTest extends TestCase
{
    public static function publicStaticProviderMethod(): array
    {
        return [
            [0, 0, 0],
            [1, 1, 0],
            [1, 3, 4],
            [0, 2, 2],
        ];
    }

    #[DataProvider('publicProviderMethod')]
    #[DataProvider('publicStaticProviderMethod')]
    #[DataProvider('protectedProviderMethod')]
    #[DataProvider('protectedStaticProviderMethod')]
    #[DataProvider('privateProviderMethod')]
    #[DataProvider('privateStaticProviderMethod')]
    public function testAdd($a, $b, $c): void
    {
        $this->assertEquals($c, $a + $b);
    }

    public function publicProviderMethod(): array
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [1, 1, 3],
            [1, 0, 1],
        ];
    }

    protected function protectedProviderMethod(): array
    {
        return [
            [3, 0, 0],
            [1, 1, 2],
            [2, 3, 5],
            [2, 1, 3],
        ];
    }

    private function privateProviderMethod(): array
    {
        return [
            [0, 1, 1],
            [1, 0, 1],
            [1, 1, 2],
            [0, 2, 2],
        ];
    }

    protected static function protectedStaticProviderMethod(): array
    {
        return [
            [1, 0, 1],
            [2, 1, 3],
            [3, 3, 6],
            [4, 1, 5],
        ];
    }

    private static function privateStaticProviderMethod(): array
    {
        return [
            [2, 0, 2],
            [1, 1, 0],
            [2, 3, 5],
            [0, 1, 1],
        ];
    }
}
