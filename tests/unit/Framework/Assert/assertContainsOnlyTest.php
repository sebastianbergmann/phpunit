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
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;

#[CoversMethod(Assert::class, 'assertContainsOnly')]
#[CoversMethod(Assert::class, 'isNativeType')]
#[TestDox('assertContainsOnly()')]
#[Small]
#[IgnorePhpunitDeprecations]
final class assertContainsOnlyTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: iterable}>
     */
    public static function successProvider(): array
    {
        return [
            ['array', [[1, 2, 3]]],
            ['boolean', [true, false]],
            ['bool', [true, false]],
            ['float', [1.0, 2.0, 3.0]],
            ['integer', [1, 2, 3]],
            ['int', [1, 2, 3]],
            ['null', [null]],
            ['numeric', [1, 2.0, '3', '4.0']],
            ['object', [new stdClass]],
            ['resource', [fopen(__FILE__, 'r')]],
            ['scalar', [true, 1.0, 1, 'string']],
            ['string', ['string']],
            [stdClass::class, [new stdClass]],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: iterable}>
     */
    public static function failureProvider(): array
    {
        return [
            ['array', [[1, 2, 3], null]],
            ['boolean', [true, false, null]],
            ['bool', [true, false, null]],
            ['float', [1.0, 2.0, 3.0, null]],
            ['integer', [1, 2, 3, null]],
            ['int', [1, 2, 3, null]],
            ['null', [null, 0]],
            ['numeric', [1, 2.0, '3', '4.0', null]],
            ['object', [new stdClass, null]],
            ['resource', [fopen(__FILE__, 'r'), null]],
            ['scalar', [true, 1.0, 1, 'string', null]],
            ['string', ['string', null]],
            [stdClass::class, [new stdClass, null]],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(string $type, iterable $haystack): void
    {
        $this->assertContainsOnly($type, $haystack);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(string $type, iterable $haystack): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnly($type, $haystack);
    }
}
