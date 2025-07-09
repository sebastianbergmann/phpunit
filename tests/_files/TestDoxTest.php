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

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class TestDoxTest extends TestCase
{
    public function testOne(): void
    {
    }

    public function testTwo(): void
    {
    }

    #[TestDox('This is a custom test description')]
    public function testThree(): void
    {
    }

    #[TestDox('This is a custom test description with placeholders $a $b $f $i $s $o $stdClass $enum $backedEnum $n $empty $default')]
    public function testFour(array $a, bool $b, float $f, int $i, string $s, object $o, object $stdClass, $enum, $backedEnum, $n, string $empty, string $default = 'default'): void
    {
    }
}
