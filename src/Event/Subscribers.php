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

use ArrayIterator;
use Iterator;

final class Subscribers
{
    /**
     * @var array<string, array<int, Subscriber>>
     */
    private array $subscribers = [];

    public function add(Subscriber $subscriber): void
    {
        foreach ($subscriber->subscribesTo() as $type) {
            $this->subscribers[$type->asString()][] = $subscriber;
        }
    }

    /**
     * @return Iterator<Subscriber>
     */
    public function for(Type $type): Iterator
    {
        return new ArrayIterator($this->subscribers[$type->asString()]);
    }
}
