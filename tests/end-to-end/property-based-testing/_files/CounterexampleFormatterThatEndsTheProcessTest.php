<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\CounterexampleFormatter;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class CounterexampleFormatterThatEndsTheProcessTest extends TestCase
{
    public static function exits(int $value): string
    {
        exit(1);
    }

    /**
     * @param int<1000, 2000> $value
     */
    #[Property]
    #[CounterexampleFormatter('exits')]
    public function testExits(int $value): void
    {
        exit(1);
    }

    /**
     * @param int<1000, 2000> $value
     */
    #[Property]
    #[TestWith([500])]
    #[CounterexampleFormatter('exits')]
    public function testExitsForExplicitExample(int $value): void
    {
        exit(1);
    }
}
