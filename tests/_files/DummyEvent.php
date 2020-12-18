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
use PHPUnit\Event\Type;

final class DummyEvent implements Event
{
    private Type $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function type(): Type
    {
        return $this->type;
    }
}
