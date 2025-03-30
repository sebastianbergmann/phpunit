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
use stdClass;

#[CoversMethod(Assert::class, 'assertNotInstanceOf')]
#[TestDox('assertNotInstanceOf()')]
#[Small]
final class assertNotInstanceOfTest extends TestCase
{
    #[DataProviderExternal(assertInstanceOfTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expected, mixed $actual): void
    {
        $this->assertNotInstanceOf($expected, $actual);
    }

    #[DataProviderExternal(assertInstanceOfTest::class, 'successProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expected, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotInstanceOf($expected, $actual);
    }

    public function testDoesNotSupportUnknownTypes(): void
    {
        $this->expectException(UnknownClassOrInterfaceException::class);
        $this->expectExceptionMessage('Class or interface "does-not-exist" does not exist');

        $this->assertNotInstanceOf('does-not-exist', new stdClass);
    }
}
