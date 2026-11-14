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

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(AfterLastTestMethodFailedException::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/property-based-testing')]
final class AfterLastTestMethodFailedExceptionTest extends TestCase
{
    public function testDescribesTheTestClassAndTheThrowableThatTheHookMethodRaised(): void
    {
        $previous  = new RuntimeException('the message');
        $exception = new AfterLastTestMethodFailedException(self::class, $previous);

        $this->assertSame(
            'A hook method of ' . self::class . ' that is called after its last test raised RuntimeException: the message',
            $exception->getMessage(),
        );
    }

    public function testDescribesAThrowableWithoutMessageWithItsClass(): void
    {
        $exception = new AfterLastTestMethodFailedException(self::class, new LogicException);

        $this->assertSame(
            'A hook method of ' . self::class . ' that is called after its last test raised LogicException',
            $exception->getMessage(),
        );
    }

    public function testHasTheTestClass(): void
    {
        $exception = new AfterLastTestMethodFailedException(self::class, new RuntimeException('the message'));

        $this->assertSame(self::class, $exception->className());
    }

    public function testHasTheThrowableThatTheHookMethodRaisedAsPreviousException(): void
    {
        $previous  = new RuntimeException('the message');
        $exception = new AfterLastTestMethodFailedException(self::class, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
