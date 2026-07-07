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
use PHPUnit\Framework\TestCase;

final class PreviousErrorHandlerSuppressedTest extends TestCase
{
    public function testSuppressedWarning(): void
    {
        @trigger_error('suppressed warning from test', E_USER_WARNING);

        $this->assertTrue(true);
    }
}
