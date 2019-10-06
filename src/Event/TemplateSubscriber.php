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

abstract class TemplateSubscriber implements Subscriber
{
    public function notify(Event $event): void
    {
        $this->ensureSupportedEventType($event);
        $this->handle($event);
    }

    abstract protected function handle(Event $event): void;

    /**
     * @throws UnsupportedEvent
     */
    private function ensureSupportedEventType(Event $event): void
    {
        if (!$this->typesSubscribedTo()->contains($event->type())) {
            throw UnsupportedEvent::type(
                static::class,
                $event->type()
            );
        }
    }
}
