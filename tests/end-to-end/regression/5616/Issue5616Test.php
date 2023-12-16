<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5616;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Issue5616Test extends TestCase
{
    public static function provider(): array
    {
        return [
            [1, '2', 3.0, true],
        ];
    }

    #[DataProvider('provider')]
    public function testOne(int $a, string $b, float $c, bool $d): void
    {
        $this->assertTrue(false);
    }
}
