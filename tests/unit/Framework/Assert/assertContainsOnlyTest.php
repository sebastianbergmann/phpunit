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

use function fopen;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;

#[CoversMethod(Assert::class, 'assertContainsOnly')]
#[TestDox('assertContainsOnly()')]
#[Small]
final class assertContainsOnlyTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: iterable}>
     */
    public static function successProvider(): array
    {
        return [
            [NativeType::Array, [[1, 2, 3]]],
            [NativeType::Boolean, [true, false]],
            [NativeType::Float, [1.0, 2.0, 3.0]],
            [NativeType::Integer, [1, 2, 3]],
            [NativeType::Null, [null]],
            [NativeType::Numeric, [1, 2.0, '3', '4.0']],
            [NativeType::Object, [new stdClass]],
            [NativeType::Resource, [fopen(__FILE__, 'r')]],
            [NativeType::Scalar, [true, 1.0, 1, 'string']],
            [NativeType::String, ['string']],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: iterable}>
     */
    public static function failureProvider(): array
    {
        return [
            [NativeType::Array, [[1, 2, 3], null]],
            [NativeType::Boolean, [true, false, null]],
            [NativeType::Float, [1.0, 2.0, 3.0, null]],
            [NativeType::Integer, [1, 2, 3, null]],
            [NativeType::Numeric, [null, 0]],
            [NativeType::Numeric, [1, 2.0, '3', '4.0', null]],
            [NativeType::Object, [new stdClass, null]],
            [NativeType::Resource, [fopen(__FILE__, 'r'), null]],
            [NativeType::Scalar, [true, 1.0, 1, 'string', null]],
            [NativeType::String, ['string', null]],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(NativeType $type, iterable $haystack): void
    {
        $this->assertContainsOnly($type, $haystack);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(NativeType $type, iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnly($type, $haystack);
    }
}
