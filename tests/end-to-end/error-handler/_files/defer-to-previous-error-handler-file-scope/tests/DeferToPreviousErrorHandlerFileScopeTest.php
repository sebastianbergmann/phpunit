<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandlerFileScope;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\TestCase;

trigger_error('please ignore this deprecation at file scope', E_USER_DEPRECATED);
trigger_error('report this deprecation at file scope', E_USER_DEPRECATED);

final class DeferToPreviousErrorHandlerFileScopeTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
