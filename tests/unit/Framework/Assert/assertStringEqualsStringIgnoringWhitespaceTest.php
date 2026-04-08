<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertStringEqualsStringIgnoringWhitespace')]
#[CoversMethod(Assert::class, 'stringEqualsStringIgnoringWhitespace')]
#[TestDox('assertStringEqualsStringIgnoringWhitespace()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertStringEqualsStringIgnoringWhitespaceTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: string, 1: string}>
     */
    public static function successProvider(): array
    {
        return [
            ['hello world', "hello\tworld"],
            ['hello world', "hello\nworld"],
            ['hello world', "hello\r\nworld"],
            ['hello world', 'hello  world'],
            ['hello world', "hello\xC2\xA0world"],
            ['hello world', "hello\xE2\x80\xAFworld"],
            ['hello world', "  hello \t world  "],
            ['', ''],
            ['a', ' a '],
        ];
    }

    /**
     * @return non-empty-list<array{0: string, 1: string}>
     */
    public static function failureProvider(): array
    {
        return [
            ['hello world', 'helloworld'],
            ['hello world', 'goodbye world'],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expected, string $actual): void
    {
        $this->assertStringEqualsStringIgnoringWhitespace($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expected, string $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringEqualsStringIgnoringWhitespace($expected, $actual);
    }
}
