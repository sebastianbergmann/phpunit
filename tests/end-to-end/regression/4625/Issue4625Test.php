<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue4625;

use PHPUnit\Framework\TestCase;

final class Issue4625Test extends TestCase
{
    public static function dataProvider(): iterable
    {
        yield 'a' => [1];

        // the key below is an array
        yield ['b'] => [2];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOne(int $x): void
    {
        $this->assertGreaterThan(0, $x);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
