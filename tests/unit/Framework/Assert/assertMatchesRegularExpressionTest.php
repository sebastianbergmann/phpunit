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

#[CoversMethod(Assert::class, 'assertMatchesRegularExpression')]
#[TestDox('assertMatchesRegularExpression()')]
#[Small]
final class assertMatchesRegularExpressionTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function successProvider(): array
    {
        return [
            ['/foo/', 'foobar'],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function failureProvider(): array
    {
        return [
            ['/foo/', 'bar'],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $pattern, string $string): void
    {
        $this->assertMatchesRegularExpression($pattern, $string);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $pattern, string $string): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertMatchesRegularExpression($pattern, $string);
    }
}
