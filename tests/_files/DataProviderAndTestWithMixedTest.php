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
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class DataProviderAndTestWithMixedTest extends TestCase
{
    public static function provider(): array
    {
        return [[1]];
    }

    #[DataProvider('provider')]
    #[TestWith([2])]
    public function testOne(int $value): void
    {
    }
}
