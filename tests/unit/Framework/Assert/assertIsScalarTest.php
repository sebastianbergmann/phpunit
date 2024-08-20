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

#[CoversMethod(Assert::class, 'assertIsScalar')]
#[TestDox('assertIsScalar()')]
#[Small]
final class assertIsScalarTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed}>
     */
    public static function successProvider(): array
    {
        return [
            [true],
            [false],
            [0],
            [0.0],
            ['string'],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed}>
     */
    public static function failureProvider(): array
    {
        $openResource = fopen(__FILE__, 'r');

        $closedResource = fopen(__FILE__, 'r');
        fclose($closedResource);

        return [
            [[]],
            [null],
            [new stdClass],
            [$openResource],
            [$closedResource],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $actual): void
    {
        $this->assertIsScalar($actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertIsScalar($actual);
    }
}
