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

#[CoversMethod(Assert::class, 'assertNotEqualsIgnoringCase')]
#[TestDox('assertNotEqualsIgnoringCase()')]
#[Small]
final class assertNotEqualsIgnoringCaseTest extends TestCase
{
    #[DataProviderExternal(assertEqualsIgnoringCaseTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $expected, mixed $actual): void
    {
        $this->assertNotEqualsIgnoringCase($expected, $actual);
    }

    #[DataProviderExternal(assertEqualsIgnoringCaseTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $expected, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotEqualsIgnoringCase($expected, $actual);
    }
}
