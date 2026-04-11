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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorHandler::class)]
#[Small]
#[Group('test-runner')]
final class ErrorHandlerTest extends TestCase
{
    public function testCanRegisterAndRestoreForNonTestCaseContext(): void
    {
        $errorHandler = ErrorHandler::instance();
        $errorHandler->registerForNonTestCaseContext();
        $errorHandler->restoreForNonTestCaseContext();

        $this->assertTrue(true);
    }
}
