<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\PropertyBased;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorTriggeredWhileBootingException::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/property-based-testing')]
final class ErrorTriggeredWhileBootingExceptionTest extends TestCase
{
    public function testDescribesAnError(): void
    {
        $exception = new ErrorTriggeredWhileBootingException(
            [
                ['test' => 'FooTest::testOne', 'message' => 'the first message'],
            ],
        );

        $this->assertSame(
            "Booting PHPUnit triggered an error:\n\n1) FooTest::testOne\nthe first message",
            $exception->getMessage(),
        );
    }

    public function testDescribesErrors(): void
    {
        $exception = new ErrorTriggeredWhileBootingException(
            [
                ['test' => 'FooTest::testOne', 'message' => 'the first message'],
                ['test' => 'FooTest::testTwo', 'message' => 'the second message'],
            ],
        );

        $this->assertSame(
            "Booting PHPUnit triggered 2 errors:\n\n1) FooTest::testOne\nthe first message\n\n2) FooTest::testTwo\nthe second message",
            $exception->getMessage(),
        );
    }
}
