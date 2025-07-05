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

class FaillingDataProviderTest extends TestCase
{
    public static function provideData(): array
    {
        return [
            'good1' => [3, 1, 2],
            'good2' => [5, 2, 3],
            'fail1' => [10, 3, 4],
            'good3' => [10, 5, 5],
            'fail2' => [20, 3, 4],
        ];
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('provideData')]
    public function testWithProvider($sum, $summand, $summmand2): void
    {
        $this->assertSame($sum, $summand + $summmand2);
    }
}
