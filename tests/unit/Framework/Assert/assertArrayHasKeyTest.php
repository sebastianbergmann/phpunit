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

use ArrayAccess;
use ArrayObject;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\TestFixture\SampleArrayAccess;

#[CoversMethod(Assert::class, 'assertArrayHasKey')]
#[TestDox('assertArrayHasKey()')]
#[Small]
final class assertArrayHasKeyTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: int|string, 1: array<mixed>|ArrayAccess<array-key, mixed>}>
     */
    public static function successProvider(): array
    {
        $arrayAccess        = new SampleArrayAccess;
        $arrayAccess['foo'] = 'bar';

        $arrayObject        = new ArrayObject;
        $arrayObject['foo'] = 'bar';

        return [
            [0, ['foo']],
            ['foo', ['foo' => 'bar']],
            ['foo', $arrayAccess],
            ['foo', $arrayObject],
        ];
    }

    /**
     * @return non-empty-list<array{0: int|string, 1: array<mixed>|ArrayAccess<array-key, mixed>}>
     */
    public static function failureProvider(): array
    {
        $arrayAccess        = new SampleArrayAccess;
        $arrayAccess['foo'] = 'bar';

        $arrayObject        = new ArrayObject;
        $arrayObject['foo'] = 'bar';

        return [
            [1, ['foo']],
            ['bar', ['foo' => 'bar']],
            ['bar', $arrayAccess],
            ['bar', $arrayObject],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(int|string $key, array|ArrayAccess $array): void
    {
        $this->assertArrayHasKey($key, $array);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(int|string $key, array|ArrayAccess $array): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey($key, $array);
    }
}
