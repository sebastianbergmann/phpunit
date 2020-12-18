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

final class Dispatcher
{
    /**
     * @var array<string, array<int, Subscriber>>
     */
    private array $subscribers = [];

    public function register(Subscriber ...$subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            foreach ($subscriber->typesSubscribedTo() as $type) {
                $this->subscribers[$type->asString()][] = $subscriber;

                if ($type instanceof SubType) {
                    $this->subscribers[$type->super()->asString()][] = $subscriber;
                }
            }
        }
    }

    public function dispatch(Event $event): void
    {
        $type = $event->type();

        if (!array_key_exists($type->asString(), $this->subscribers)) {
            return;
        }

        foreach ($this->subscribers[$type->asString()] as $subscriber) {
            $subscriber->notify($event);
        }
    }
}
