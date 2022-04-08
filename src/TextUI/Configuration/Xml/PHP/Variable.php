<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Variable
{
    private string $name;
    private mixed $value;
    private bool $force;

    public function __construct(string $name, $value, bool $force)
    {
        $this->name  = $name;
        $this->value = $value;
        $this->force = $force;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function force(): bool
    {
        return $this->force;
    }
}
