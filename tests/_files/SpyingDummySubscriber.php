<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
final class SpyingDummySubscriber implements DummySubscriber
{
    private $events = [];

    public function notify(DummyEvent $event): void
    {
        $this->events[] = $event;
    }

    public function events(): array
    {
        return $this->events;
    }
}
