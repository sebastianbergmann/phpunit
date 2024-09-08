<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5891;

use PHPUnit\Framework\TestCase;

final class Issue5891Test extends TestCase
{
    public function testVariadicParam(): void
    {
        $mock = $this->createMock(ArgumentList::class);
        $mock
            ->method('foo')
            ->with($this->callback(static function (...$items): bool
            {
                self::assertSame([1, 2, 3], $items);

                return true;
            }));

        $mock->foo(1, 2, 3);
    }

    public function testTwoParametersAndVariadicParam(): void
    {
        $mock = $this->createMock(TwoParametersAndArgumentList::class);
        $mock
            ->method('foo')
            ->with($this->callback(static function (...$items): bool
            {
                self::assertSame(['1st', '2nd', '3rd', '4th'], $items);

                return true;
            }));

        $mock->foo('1st', '2nd', '3rd', '4th');
    }
}

interface ArgumentList
{
    public function foo(int ...$items): void;
}

interface TwoParametersAndArgumentList
{
    public function foo(string $first, string $second, string ...$rest): void;
}
