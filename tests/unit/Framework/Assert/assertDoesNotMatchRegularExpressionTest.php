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
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertDoesNotMatchRegularExpression')]
#[TestDox('assertDoesNotMatchRegularExpression()')]
#[Small]
final class assertDoesNotMatchRegularExpressionTest extends TestCase
{
    #[DataProviderExternal(assertMatchesRegularExpressionTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $pattern, string $string): void
    {
        $this->assertDoesNotMatchRegularExpression($pattern, $string);
    }

    #[DataProviderExternal(assertMatchesRegularExpressionTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $pattern, string $string): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertDoesNotMatchRegularExpression($pattern, $string);
    }
}
