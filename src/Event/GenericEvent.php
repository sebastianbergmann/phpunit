<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

final class GenericEvent implements Event
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
