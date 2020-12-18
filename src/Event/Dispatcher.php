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

final class Dispatcher
{
    private Subscribers $subscribers;

    public function __construct()
    {
        $this->subscribers = new Subscribers();
    }

    public function register(Subscriber ...$subscribers): void
    {
        $this->subscribers->add(...$subscribers);
    }

    public function dispatch(Event $event): void
    {
        foreach ($this->subscribers->for($event->type()) as $subscriber) {
            $subscriber->notify($event);
        }
    }
}
