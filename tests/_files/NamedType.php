<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Event\Type;

final class NamedType implements Type
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function is(Type $other): bool
    {
        return $other->asString() === $this->asString();
    }

    public function asString(): string
    {
        return $this->name;
    }
}
