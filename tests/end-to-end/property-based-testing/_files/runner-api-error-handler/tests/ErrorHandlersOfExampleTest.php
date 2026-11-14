<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use function restore_error_handler;
use function set_error_handler;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class ErrorHandlersOfExampleTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function integersWhoseGenerationRegistersAnErrorHandler(): ValueGenerator
    {
        return Generators::integers(0, 9)->map(
            static function (int $value): int
            {
                set_error_handler(static fn (): bool => true);

                return $value;
            },
        );
    }

    #[Property]
    public function testGeneratorDoesNotRemoveTheErrorHandlerThatItRegistered(#[ForAll('integersWhoseGenerationRegistersAnErrorHandler')] int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testRemovesMoreErrorHandlersThanItRegistered(int $value): void
    {
        restore_error_handler();
        restore_error_handler();
        restore_error_handler();

        $this->assertGreaterThanOrEqual(0, $value);
    }
}
