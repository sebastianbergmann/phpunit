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
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;

#[CoversMethod(Assert::class, 'assertInstanceOf')]
#[CoversClass(UnknownClassOrInterfaceException::class)]
#[TestDox('assertInstanceOf()')]
#[Small]
final class assertInstanceOfTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: mixed}>
     */
    public static function successProvider(): array
    {
        return [
            [stdClass::class, new stdClass],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: mixed}>
     */
    public static function failureProvider(): array
    {
        return [
            [self::class, new stdClass],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $expected, mixed $actual): void
    {
        $this->assertInstanceOf($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $expected, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertInstanceOf($expected, $actual);
    }

    public function testDoesNotSupportUnknownTypes(): void
    {
        $this->expectException(UnknownClassOrInterfaceException::class);
        $this->expectExceptionMessage('Class or interface "does-not-exist" does not exist');

        $this->assertInstanceOf('does-not-exist', new stdClass);
    }
}
