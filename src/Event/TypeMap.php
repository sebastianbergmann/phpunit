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
use function in_array;
use function interface_exists;
use function sprintf;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TypeMap
{
    /**
     * @psalm-var array<class-string, class-string>
     */
    private array $mapping = [];

    /**
     * @psalm-param class-string $subscriberInterface
     * @psalm-param class-string $eventClass
     *
     * @throws EventAlreadyAssignedException
     * @throws InvalidEventException
     * @throws InvalidSubscriberException
     * @throws SubscriberTypeAlreadyRegisteredException
     * @throws UnknownEventException
     * @throws UnknownSubscriberException
     */
    public function addMapping(string $subscriberInterface, string $eventClass): void
    {
        if (!interface_exists($subscriberInterface)) {
            throw new UnknownSubscriberException(
                sprintf(
                    'Subscriber "%s" does not exist or is not an interface',
                    $subscriberInterface,
                ),
            );
        }

        if (!class_exists($eventClass)) {
            throw new UnknownEventException(
                sprintf(
                    'Event class "%s" does not exist',
                    $eventClass,
                ),
            );
        }

        if (!in_array(Subscriber::class, class_implements($subscriberInterface), true)) {
            throw new InvalidSubscriberException(
                sprintf(
                    'Subscriber "%s" does not implement Subscriber interface',
                    $subscriberInterface,
                ),
            );
        }

        if (!in_array(Event::class, class_implements($eventClass), true)) {
            throw new InvalidEventException(
                sprintf(
                    'Event "%s" does not implement Event interface',
                    $eventClass,
                ),
            );
        }

        if (array_key_exists($subscriberInterface, $this->mapping)) {
            throw new SubscriberTypeAlreadyRegisteredException(
                sprintf(
                    'Subscriber type "%s" already registered - cannot overwrite',
                    $subscriberInterface,
                ),
            );
        }

        if (in_array($eventClass, $this->mapping, true)) {
            throw new EventAlreadyAssignedException(
                sprintf(
                    'Event "%s" already assigned - cannot add multiple subscriber types for an event type',
                    $eventClass,
                ),
            );
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
        return in_array($event::class, $this->mapping, true);
    }

    /**
     * @psalm-return class-string
     *
     * @throws MapError
     */
    public function map(Subscriber $subscriber): string
    {
        foreach (class_implements($subscriber) as $interface) {
            if (array_key_exists($interface, $this->mapping)) {
                return $this->mapping[$interface];
            }
        }

        throw new MapError(
            sprintf(
                'Subscriber "%s" does not implement a known interface',
                $subscriber::class,
            ),
        );
    }
}
