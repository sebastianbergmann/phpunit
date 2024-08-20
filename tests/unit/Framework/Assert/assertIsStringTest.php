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

#[CoversMethod(Assert::class, 'assertIsString')]
#[TestDox('assertIsString()')]
#[Small]
final class assertIsStringTest extends TestCase
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
            [[]],
            [true],
            [0.0],
            [0],
            [null],
            [new stdClass],
            [$openResource],
            [$closedResource],
        ];
    }

    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertIsString('string');
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertIsString($actual);
    }
}
