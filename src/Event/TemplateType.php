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

abstract class TemplateType implements Type
{
    final public function is(Type $other): bool
    {
        return $this->asString() === $other->asString();
    }
}
