<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5288;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Issue5288Test extends TestCase
{
    public static function provider(): array
    {
        return [[true]];
    }

    #[DataProvider('provider')]
    public function testOne(bool $value): void
    {
        $this->assertTrue($value);
    }
}
