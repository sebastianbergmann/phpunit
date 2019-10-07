<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use function sprintf;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\UnexpectedEvent
 */
final class UnexpectedEventTest extends TestCase
{
    public function testForReturnsUnexpectedEvent(): void
    {
        $subscriberClassName = self::class;

        $type = new GenericType('foo');

        $exception = UnexpectedEvent::for(
            $subscriberClassName,
            $type
        );

        $message = sprintf(
            'Subscriber "%s" is not subscribed to events of type "%s".',
            $subscriberClassName,
            $type->asString()
        );

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(\PHPUnit\Exception::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
