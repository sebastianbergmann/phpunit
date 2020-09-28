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
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\InvalidArgumentException;

/**
 * Constraint that accepts classes that implement given interface.
 *
 * @extends AbstractInheritanceConstraint<ImplementsInterface>
 */
final class ImplementsInterface extends AbstractInheritanceConstraint
{
    /**
     * @throws InvalidArgumentException
     *
     * @psalm-assert class-string $interface
     */
    public static function fromInterfaceString(string $interface): self
    {
        if (!interface_exists($interface)) {
            throw InvalidArgumentException::create(1, 'interface-string');
        }

        return new self($interface);
    }

    /**
     * Returns short description of what we examine, e.g. ``'impements interface'``.
     */
    protected function verb(bool $negated = false): string
    {
        if ($negated) {
            return 'does not implement interface';
        }

        return 'implements interface';
    }

    /**
     * Returns an array of interfaces $class implements.
     */
    protected function inheritance(string $class): array
    {
        return class_implements($class);
    }

    /**
     * Checks if *$string* may be used as an argument to ``getInheritedClassesFor()``.
     *
     * @psalm-assert-if-true class-string $class
     */
    protected function supportsActual(string $class): bool
    {
        return class_exists($class) || interface_exists($class);
    }
}

// vim: syntax=php sw=4 ts=4 et:
