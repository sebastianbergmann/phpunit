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
        $variadicParam = $this->createMock(VariadicParam::class);
        $variadicParam
            ->method('foo')
            ->with($this->callback(static function (...$items): bool
            {
                self::assertSame([1, 2, 3], $items);

                return true;
            }));

        $variadicParam->foo(1, 2, 3);
    }

    public function testTwoParametersAndVariadicParam(): void
    {
        $twoParametersAndVariadicParam = $this->createMock(TwoParametersAndVariadicParam::class);
        $twoParametersAndVariadicParam
            ->method('foo')
            ->with($this->callback(static function ($head, ...$tail): bool
            {
                self::assertSame('1st', $head);
                self::assertSame(['2nd', '3rd', '4th'], $tail);

                return true;
            }));

        $twoParametersAndVariadicParam->foo('1st', '2nd', '3rd', '4th');
    }
}

interface VariadicParam
{
    public function foo(int ...$items): void;
}
interface TwoParametersAndVariadicParam
{
    public function foo(string $first, string $second, string ...$rest): void;
}
