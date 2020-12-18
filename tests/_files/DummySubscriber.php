<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Event\Event;
use PHPUnit\Event\Subscriber;
use PHPUnit\Event\Types;

final class DummySubscriber implements Subscriber
{
    private Types $types;

    public function __construct(Types $types)
    {
        $this->types = $types;
    }

    public function subscribesTo(): Types
    {
        return $this->types;
    }

    public function notify(Event $event): void
    {
    }
}
