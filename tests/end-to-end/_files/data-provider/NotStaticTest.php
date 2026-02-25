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

final class NotStaticTest extends TestCase
{
    public function values(): array
    {
        return [[true]];
    }

    #[DataProvider('values')]
    public function testOne(bool $value): void
    {
        $this->assertTrue($value);
    }
}
