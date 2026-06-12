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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\Success;

#[CoversClass(RetryTestSuite::class)]
#[Small]
final class RetryTestSuiteTest extends TestCase
{
    public function testHoldsOneTestRegardlessOfMaximumNumberOfAttempts(): void
    {
        $suite = RetryTestSuite::fromTestCase(
            'PHPUnit\TestFixture\Success::testOne',
            new Success('testOne'),
            3,
            static fn (): TestCase => new Success('testOne'),
        );

        $this->assertCount(1, $suite);
        $this->assertSame(3, $suite->maxAttempts());
    }

    public function testNameReturnsValueProvidedToFactory(): void
    {
        $suite = RetryTestSuite::fromTestCase(
            'PHPUnit\TestFixture\Success::testOne',
            new Success('testOne'),
            3,
            static fn (): TestCase => new Success('testOne'),
        );

        $this->assertSame('PHPUnit\TestFixture\Success::testOne', $suite->name());
    }

    public function testSortIdDelegatesToFirstAttempt(): void
    {
        $test = new Success('testOne');

        $suite = RetryTestSuite::fromTestCase(
            'PHPUnit\TestFixture\Success::testOne',
            $test,
            3,
            static fn (): TestCase => new Success('testOne'),
        );

        $this->assertSame($test->sortId(), $suite->sortId());
    }
}
