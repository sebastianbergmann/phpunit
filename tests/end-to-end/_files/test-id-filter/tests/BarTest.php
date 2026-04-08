<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestIdFilter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BarTest extends TestCase
{
    public static function provideData(): array
    {
        return [
            'total ($100)'     => [true],
            'percentage (50%)' => [true],
            'path/to/file.txt' => [true],
        ];
    }

    #[DataProvider('provideData')]
    public function testWithSpecialCharacters(bool $value): void
    {
        $this->assertTrue($value);
    }
}
