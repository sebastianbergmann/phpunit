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

final class DataProviderNamedArgumentsTest extends TestCase
{
    public static function providerMethod()
    {
        return [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['c' => 3, 'a' => 2, 'b' => 1],
        ];
    }

    #[DataProvider('providerMethod')]
    public function testAdd($a, $b, $c): void
    {
        $this->assertEquals($c, $a + $b);
    }
}
