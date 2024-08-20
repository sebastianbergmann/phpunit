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

use Countable;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertNotSameSize')]
#[TestDox('assertNotSameSize()')]
#[Small]
final class assertNotSameSizeTest extends TestCase
{
    #[DataProviderExternal(assertSameSizeTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(Countable|iterable $expected, Countable|iterable $actual): void
    {
        $this->assertNotSameSize($expected, $actual);
    }

    #[DataProviderExternal(assertSameSizeTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(Countable|iterable $expected, Countable|iterable $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotSameSize($expected, $actual);
    }

    #[DataProviderExternal(assertSameSizeTest::class, 'errorProvider')]
    public function testDoesNotSupportGenerators(Countable|iterable $expected, Countable|iterable $actual): void
    {
        $this->expectException(GeneratorNotSupportedException::class);

        $this->assertNotSameSize($expected, $actual);
    }
}
