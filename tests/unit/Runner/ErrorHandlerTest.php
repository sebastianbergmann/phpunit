<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(ErrorHandler::class)]
#[Small]
final class ErrorHandlerTest extends TestCase
{
    public function testThrowsExceptionWhenUsingInvalidOrderOption(): void
    {
        $errorHandler = ErrorHandler::instance();
        $errorHandler->registerDeprecationHandler();
        trigger_error('deprecation', E_USER_DEPRECATED);
        $errorHandler->restoreDeprecationHandler();
        $refl                   = new ReflectionClass($errorHandler);
        $globalDeprecations     = $refl->getProperty('globalDeprecations');
        $registeredDeprecations = $globalDeprecations->getValue($errorHandler);
        $this->assertCount(1, $registeredDeprecations);
        $this->assertSame('deprecation', $registeredDeprecations[0][1]);
        $globalDeprecations->setValue($errorHandler, []);
    }
}
