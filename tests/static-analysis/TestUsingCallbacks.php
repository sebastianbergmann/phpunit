<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\StaticAnalysis;

use PHPUnit\Framework\TestCase;

/** @see https://www.youtube.com/watch?v=rXwMrBb2x1Q */
interface SayHello
{
    public function hey(string $toPerson): string;
}

/** @small */
final class TestUsingCallbacks extends TestCase
{
    public function testWillSayHelloAndCheckCallbackInput(): void
    {
        $mock = $this->createMock(SayHello::class);

        $mock
            ->expects(self::once())
            ->method('hey')
            ->with(self::callback(static function (string $input): bool {
                self::assertStringContainsString('Joe', $input);

                return true;
            }))
            ->willReturn('Hey Joe!');

        self::assertSame('Hey Joe!', $mock->hey('Joe'));
    }

    public function testWillSayHelloAndCheckCallbackWithoutAnyInput(): void
    {
        $mock = $this->createMock(SayHello::class);

        $mock
            ->expects(self::once())
            ->method('hey')
            ->with(self::callback(static function (): bool {
                return true;
            }))
            ->willReturn('Hey Joe!');

        self::assertSame('Hey Joe!', $mock->hey('Joe'));
    }
}
