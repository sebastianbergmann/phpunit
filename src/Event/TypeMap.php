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

use function array_key_exists;
use function class_exists;
use function class_implements;
use function get_class;
use function in_array;
use function interface_exists;
use function sprintf;
use RuntimeException;

class TypeMap
{
    /**
     * @var array<class-string, class-string>
     */
    private array $mapping = [];

    /**
     * @throws RuntimeException
     */
    public function addMapping(string $subscriberInterface, string $eventClass): void
    {
        if (!interface_exists($subscriberInterface, true)) {
            throw new RuntimeException(sprintf(
                'Subscriber "%s" does not exist or is not an interface',
                $subscriberInterface
            ));
        }

        if (!class_exists($eventClass, true)) {
            throw new RuntimeException(sprintf(
                'Event class "%s" does not exist',
                $eventClass
            ));
        }

        if (!in_array(Subscriber::class, class_implements($subscriberInterface), true)) {
            throw new RuntimeException(sprintf(
                'Subscriber "%s" does not implement Subscriber interface',
                $subscriberInterface
            ));
        }

        if (!in_array(Event::class, class_implements($eventClass), true)) {
            throw new RuntimeException(sprintf(
                'Event "%s" does not implement Event interface',
                $eventClass
            ));
        }

        if (array_key_exists($subscriberInterface, $this->mapping)) {
            throw new RuntimeException(sprintf(
                'Subscriber "%s" already registered - cannot overwrite',
                $subscriberInterface
            ));
        }

        if (in_array($eventClass, $this->mapping, true)) {
            throw new RuntimeException(sprintf(
                'Event "%s" already assigned - cannot add multiple subscribers for an event type',
                $eventClass
            ));
        }

        $this->mapping[$subscriberInterface] = $eventClass;
    }

    public function isKnownSubscriberType(Subscriber $subscriber): bool
    {
        foreach (class_implements($subscriber) as $interface) {
            if (array_key_exists($interface, $this->mapping)) {
                return true;
            }
        }

        return false;
    }

    public function isKnownEventType(Event $event): bool
    {
        return in_array(get_class($event), $this->mapping, true);
    }

    /**
     * @throws RuntimeException
     */
    public function map(Subscriber $subscriber): string
    {
        foreach (class_implements($subscriber) as $interface) {
            if (array_key_exists($interface, $this->mapping)) {
                return $this->mapping[$interface];
            }
        }

        throw new RuntimeException(sprintf(
            'Subscriber "%s" does not implement a known interface',
            get_class($subscriber)
        ));
    }
}
