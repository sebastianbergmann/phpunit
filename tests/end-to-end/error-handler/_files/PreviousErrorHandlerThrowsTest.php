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

use const E_USER_WARNING;
use function trigger_error;
use ErrorException;
use PHPUnit\Framework\TestCase;

final class PreviousErrorHandlerThrowsTest extends TestCase
{
    public function testWarningIsTurnedIntoException(): void
    {
        $this->expectException(ErrorException::class);

        trigger_error('warning from test', E_USER_WARNING);
    }
}
