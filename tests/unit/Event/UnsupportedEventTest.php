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
use NamedType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\UnsupportedEvent
 */
final class UnsupportedEventTest extends TestCase
{
    public function testTypeReturnsUnsupportedEvent(): void
    {
        $type = new NamedType('foo');

        $exception = UnsupportedEvent::type($type);

        $message = sprintf(
            'Type "%s" not supported',
            $type->asString()
        );

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(\PHPUnit\Exception::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
