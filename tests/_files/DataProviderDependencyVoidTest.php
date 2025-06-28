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

final class DataProviderDependencyVoidTest extends TestCase
{
    public static function provider(): iterable
    {
        return [
            [0, 0],
            [1, 'b' => 1],
            ['a' => 2, 'b' => 2],
            ['b' => 3, 'a' => 3],
        ];
    }

    #[DataProvider('provider')]
    #[Depends('testDependency')]
    public function testEquality($a, $b): void
    {
        $this->assertSame($a, $b);
    }

    public function testDependency(): void
    {
        $this->assertTrue(true);
    }
}
