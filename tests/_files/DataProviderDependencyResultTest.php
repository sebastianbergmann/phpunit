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
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class DataProviderDependencyResultTest extends TestCase
{
    public static function providerMethod(): array
    {
        return [
            [0, 2],
            [1, 1],
            ['b' => 2, 'a' => 0],
        ];
    }

    #[DataProvider('providerMethod')]
    #[Depends('testDependency')]
    public function testAdd($a, $b, $c): void
    {
        $this->assertSame(2, $c);
        $this->assertSame($c, $a + $b);
    }

    public function testDependency(): int
    {
        $a = 2;
        $this->assertSame(2, $a);

        return $a;
    }
}
