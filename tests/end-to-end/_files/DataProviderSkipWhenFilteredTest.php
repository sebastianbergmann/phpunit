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

final class DataProviderSkipWhenFilteredTest extends TestCase
{
    public static function providerForA(): array
    {
        return [[1], [2]];
    }

    public static function providerForB(): array
    {
        return [[1], [2]];
    }

    #[DataProvider('providerForA')]
    public function testA(int $i): void
    {
        $this->assertGreaterThan(0, $i);
    }

    #[DataProvider('providerForB')]
    public function testB(int $i): void
    {
        $this->assertGreaterThan(0, $i);
    }
}
