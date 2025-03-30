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

final class DuplicateKeyDataProvidersTest extends TestCase
{
    public static function dataProvider1(): iterable
    {
        return [
            'bar' => [1],
        ];
    }

    public static function dataProvider2(): iterable
    {
        return [
            'bar' => [2],
        ];
    }

    #[DataProvider('dataProvider1')]
    #[DataProvider('dataProvider2')]
    public function test($value): void
    {
        $this->assertSame(2, $value);
    }
}
