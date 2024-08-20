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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertStringEqualsStringIgnoringLineEndings')]
#[TestDox('assertStringEqualsStringIgnoringLineEndings()')]
#[Small]
final class assertStringEqualsStringIgnoringLineEndingsTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function successProvider(): array
    {
        return [
            ["a\nb", "a\r\nb"],
            ["a\rb", "a\r\nb"],
            ["a\r\nb", "a\r\nb"],
            ["a\nb", "a\rb"],
            ["a\rb", "a\rb"],
            ["a\r\nb", "a\rb"],
            ["a\nb", "a\nb"],
            ["a\rb", "a\nb"],
            ["a\r\nb", "a\nb"],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function failureProvider(): array
    {
        return [
            ["a\nb", 'ab'],
            ["a\rb", 'ab'],
            ["a\r\nb", 'ab'],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expected, string $actual): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expected, string $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringEqualsStringIgnoringLineEndings($expected, $actual);
    }
}
