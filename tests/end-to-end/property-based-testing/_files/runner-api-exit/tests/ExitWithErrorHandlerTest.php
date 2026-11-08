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

use function set_error_handler;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class ExitWithErrorHandlerTest extends TestCase
{
    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testRegistersErrorHandlersAndEndsTheProcess(int $value): void
    {
        set_error_handler(static fn (): bool => false);
        set_error_handler(static fn (): bool => false);

        exit(0);
    }
}
