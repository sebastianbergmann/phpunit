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

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestDoxFormatter;
use PHPUnit\Framework\TestCase;

final class TestDoxTest extends TestCase
{
    public static function formatter(DateTimeImmutable $date): string
    {
        return 'This is a custom description: ' . $date->format('Y-m-d');
    }

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

    #[TestDoxFormatter('formatter')]
    public function testFive(DateTimeImmutable $date): void
    {
    }

    #[TestDox('Value of a is $a, value of b is $b')]
    public function testSix(string $a, string $b): void
    {
    }
}
