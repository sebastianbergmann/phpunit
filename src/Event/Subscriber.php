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

interface Subscriber
{
    public function typesSubscribedTo(): Types;

    /**
     * @throws UnsupportedEvent
     */
    public function notify(Event $event);
}
