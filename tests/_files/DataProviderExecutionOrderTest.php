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

final class DataProviderExecutionOrderTest extends TestCase
{
    public static function dataProviderAdditions(): array
    {
        return [
            '1+2=3' => [1, 2, 3],
            '2+1=3' => [2, 1, 3],
            '1+1=3' => [1, 1, 3],
        ];
    }

    public function testFirstTestThatAlwaysWorks(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('dataProviderAdditions')]
    public function testAddNumbersWithDataProvider(int $a, int $b, int $sum): void
    {
        $this->assertSame($sum, $a + $b);
    }

    public function testTestInTheMiddleThatAlwaysWorks(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('dataProviderAdditions')]
    public function testAddMoreNumbersWithDataProvider(int $a, int $b, int $sum): void
    {
        $this->assertSame($sum, $a + $b);
    }
}
