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

final class NumericStringKeysTest extends TestCase
{
    /**
     * PHP canonicalizes integer-like keys such as '0' to integers, but leaves
     * keys such as '1.5' alone.
     */
    public static function values(): array
    {
        return [
            '1.5'  => [1],
            '1.9'  => [2],
            '0123' => [3],
            0      => [4],
        ];
    }

    #[DataProvider('values')]
    public function testOne(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }
}
