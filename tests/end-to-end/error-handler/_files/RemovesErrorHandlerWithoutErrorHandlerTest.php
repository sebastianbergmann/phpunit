<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler;

use function restore_error_handler;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

final class RemovesErrorHandlerWithoutErrorHandlerTest extends TestCase
{
    #[WithoutErrorHandler]
    public function testRemovesErrorHandlerThatItDidNotRegister(): void
    {
        restore_error_handler();

        $this->assertTrue(true);
    }
}
