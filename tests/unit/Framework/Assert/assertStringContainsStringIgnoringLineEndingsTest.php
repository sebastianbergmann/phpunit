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

#[CoversMethod(Assert::class, 'assertStringContainsStringIgnoringLineEndings')]
#[TestDox('assertStringContainsStringIgnoringLineEndings()')]
#[Small]
final class assertStringContainsStringIgnoringLineEndingsTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function successProvider(): array
    {
        return [
            ["b\nc", "b\r\nc"],
            ["b\nc", "a\r\nb\r\nc\r\nd"],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function failureProvider(): array
    {
        return [
            ["a\nc", "b\r\nc"],
            ["a\nc", "a\r\nb\r\nc\r\nd"],
            ["b\nc", "\r\nc\r\n"],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $needle, string $haystack): void
    {
        $this->assertStringContainsStringIgnoringLineEndings($needle, $haystack);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $needle, string $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringContainsStringIgnoringLineEndings($needle, $haystack);
    }
}
