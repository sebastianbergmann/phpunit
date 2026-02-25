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

final class ExpectsArgumentTest extends TestCase
{
    public static function values(int $n): array
    {
        return [[$n]];
    }

    #[DataProvider('values')]
    public function testOne(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }
}
