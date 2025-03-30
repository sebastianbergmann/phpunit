<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6115;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

enum Enumeration: int
{
    case A = 1;
}

final class Issue6115Test extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                Enumeration::A,
            ],
        ];
    }

    #[DataProvider('provider')]
    #[TestDox('$enumeration')]
    public function testOne(Enumeration $enumeration): void
    {
        $this->assertTrue(true);
    }
}
