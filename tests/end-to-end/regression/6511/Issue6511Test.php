<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6511;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class Issue6511Test extends TestCase
{
    public static function provideOneCases(): iterable
    {
        yield [
            'c' => 'Charlie',
            'a' => 'Alfa',
            'b' => 'Bravo',
        ];
    }

    #[TestDox('The a is $a, the b is $b, the c is $c.')]
    #[DataProvider('provideOneCases')]
    public function testOne($a, $b, $c): void
    {
        $this->assertSame('Alfa', $a);
        $this->assertSame('Bravo', $b);
        $this->assertSame('Charlie', $c);
    }
}
