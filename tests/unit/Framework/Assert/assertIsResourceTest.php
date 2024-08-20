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

#[CoversMethod(Assert::class, 'assertIsResource')]
#[TestDox('assertIsResource()')]
#[Small]
final class assertIsResourceTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed}>
     */
    public static function successProvider(): array
    {
        $openResource = fopen(__FILE__, 'r');

        $closedResource = fopen(__FILE__, 'r');
        fclose($closedResource);

        return [
            [$openResource],
            [$closedResource],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed}>
     */
    public static function failureProvider(): array
    {
        return [
            [true],
            [0.0],
            [0],
            [null],
            ['123'],
            ['string'],
            [new stdClass],
        ];
    }

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $actual): void
    {
        $this->assertIsResource($actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertIsResource($actual);
    }
}
