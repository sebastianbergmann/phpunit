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
use PHPUnit\Exception;

final class UnsupportedEvent extends \Exception implements Exception
{
    public static function type(string $subscriberClassName, Type $type): self
    {
        return new self(sprintf(
            'Subscriber "%s" is not subscribed to events of type "%s".',
            $subscriberClassName,
            $type->asString()
        ));
    }
}
