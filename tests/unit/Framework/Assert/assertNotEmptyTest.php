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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertNotEmpty')]
#[CoversClass(GeneratorNotSupportedException::class)]
#[TestDox('assertNotEmpty()')]
#[Small]
final class assertNotEmptyTest extends TestCase
{
    #[DataProviderExternal(assertEmptyTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $actual): void
    {
        $this->assertNotEmpty($actual);
    }

    #[DataProviderExternal(assertEmptyTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotEmpty($actual);
    }

    public function testDoesNotSupportGenerators(): void
    {
        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $actual parameter is not supported');

        $this->assertNotEmpty(f());
    }
}
