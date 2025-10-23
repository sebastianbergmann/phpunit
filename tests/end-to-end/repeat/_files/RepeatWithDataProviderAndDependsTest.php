<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class RepeatWithDataProviderAndDependsTest extends TestCase
{
    public static function provide1(): iterable
    {
        yield [true];

        yield [true];
    }

    public static function provide2(): iterable
    {
        yield [true];

        yield [false];

        yield [true];
    }

    #[DataProvider('provide1')]
    public function test1(bool $bool): void
    {
        $this->assertTrue($bool);
    }

    #[Depends('test1')]
    public function testDependsOn1(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('provide2')]
    public function test2(bool $bool): void
    {
        $this->assertTrue($bool);
    }

    #[Depends('test2')]
    public function testDependsOn2(): void
    {
        $this->assertTrue(true);
    }
}
