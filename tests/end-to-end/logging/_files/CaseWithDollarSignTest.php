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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class CaseWithDollarSignTest extends TestCase
{
    public static function dataProvider(): iterable
    {
        yield ['$12.34'];

        yield ['Some text before the price $5.67'];

        yield ['Dollar sign followed by letter $Q'];

        yield ['Alone $ surrounded by spaces'];
    }

    #[DataProvider('dataProvider')]
    #[TestDox('The "$x" is used for this test')]
    public function testSomething(string $x): void
    {
        $this->assertTrue(true);
    }
}
