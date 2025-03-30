<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function count;
use function end;
use PHPUnit\Event;

abstract class RecordingSubscriber
{
    /**
     * @var array<int, Event\Event>
     */
    private array $events = [];

    final public function recordedEventCount(): int
    {
        return count($this->events);
    }

    final public function lastRecordedEvent(): ?Event\Event
    {
        if ([] === $this->events) {
            return null;
        }

        return end($this->events);
    }

    final protected function record($event): void
    {
        $this->events[] = $event;
    }
}
