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

use function fclose;
use function fopen;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;

#[CoversMethod(Assert::class, 'assertIsList')]
#[TestDox('assertIsList()')]
#[Small]
final class assertIsListTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed}>
     */
    public static function failureProvider(): array
    {
        $openResource = fopen(__FILE__, 'r');

        $closedResource = fopen(__FILE__, 'r');
        fclose($closedResource);

        return [
            [['foo' => 'bar']],
            [[1 => 'bar', 4 => 'baz']],
            [true],
            [0.0],
            [0],
            [null],
            ['123'],
            ['string'],
            [new stdClass],
            [$openResource],
            [$closedResource],
        ];
    }

    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertIsList([1, 2, 3]);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertIsList($actual);
    }
}
