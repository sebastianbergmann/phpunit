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

use function PHPUnit\TestFixture\Generator\f;
use Countable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertNotCount')]
#[CoversClass(GeneratorNotSupportedException::class)]
#[TestDox('assertNotCount()')]
#[Small]
final class assertNotCountTest extends TestCase
{
    #[DataProviderExternal(assertCountTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(int $expectedCount, Countable|iterable $haystack): void
    {
        $this->assertNotCount($expectedCount, $haystack);
    }

    #[DataProviderExternal(assertCountTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(int $expectedCount, Countable|iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotCount($expectedCount, $haystack);
    }

    public function testDoesNotSupportGenerators(): void
    {
        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $haystack parameter is not supported');

        $this->assertNotCount(0, f());
    }
}
