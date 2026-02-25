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

final class NotPublicTest extends TestCase
{
    #[DataProvider('values')]
    public function testOne(bool $value): void
    {
        $this->assertTrue($value);
    }

    private static function values(): array
    {
        return [[true]];
    }
}
