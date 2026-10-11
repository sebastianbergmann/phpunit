<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use function restore_error_handler;
use function set_error_handler;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

final class PropertyWithoutErrorHandlerTest extends TestCase
{
    #[Property(examples: 10)]
    #[WithoutErrorHandler]
    public function testIsCheckedWithoutTheErrorHandler(int $value): void
    {
        $errorHandler = set_error_handler(static fn (): bool => false);

        restore_error_handler();

        $this->assertNull($errorHandler);
    }
}
