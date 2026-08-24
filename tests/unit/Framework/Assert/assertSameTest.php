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

use const INF;
use function array_merge;
use function fopen;
use function log;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\TestFixture\SampleClass;

#[CoversMethod(Assert::class, 'assertSame')]
#[TestDox('assertSame()')]
#[Small]
final class assertSameTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function successProvider(): array
    {
        return self::sameValues();
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function failureProvider(): array
    {
        return array_merge(assertEqualsTest::notEqualValues(), assertEqualsTest::equalValues());
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function sameValues(): array
    {
        $object   = new SampleClass(4, 8, 15);
        $file     = TEST_FILES_PATH . 'foo.xml';
        $resource = fopen($file, 'r');

        return [
            // null
            [null, null],
            // strings
            ['a', 'a'],
            // integers
            [0, 0],
            // floats
            [1.0, 1.0],
            [2.3, 2.3],
            [1 / 3, 1 / 3],
            [1 - 2 / 3, 1 - 2 / 3],
            [5.5E+123, 5.5E+123],
            [5.5E-123, 5.5E-123],
            [log(0), log(0)],
            [INF, INF],
            [-INF, -INF],
            // arrays
            [[], []],
            [[0 => 1], [0 => 1]],
            [[0 => null], [0 => null]],
            [['a', 'b' => [1, 2]], ['a', 'b' => [1, 2]]],
            // objects
            [$object, $object],
            // resources
            [$resource, $resource],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $expected, mixed $actual): void
    {
        $this->assertSame($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $expected, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertSame($expected, $actual);
    }
}
