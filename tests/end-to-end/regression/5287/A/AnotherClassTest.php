<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5278\A;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Issue5278\C\MyClassTest;

final class AnotherClassTest extends TestCase
{
    public static function provide(): array
    {
        return [[MyClassTest::VALUE]];
    }

    #[DataProvider('provide')]
    public function test(bool $value): void
    {
        $this->assertTrue($value);
    }
}
