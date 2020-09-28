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
 * Constraint that accepts classes that extend given class.
 *
 * @extends AbstractInheritanceConstraint<ExtendsClass>
 */
final class ExtendsClass extends AbstractInheritanceConstraint
{
    /**
     * @throws InvalidArgumentException
     *
     * @psalm-assert class-string $class
     */
    public static function fromClassString(string $class): self
    {
        if (!class_exists($class)) {
            throw InvalidArgumentException::create(1, 'class-string');
        }

        return new self($class);
    }

    /**
     * Returns short description of what we examine, e.g. ``'impements interface'``.
     */
    protected function verb(bool $negated = false): string
    {
        if ($negated) {
            return 'does not extend class';
        }

        return 'extends class';
    }

    /**
     * Returns an array of parent classes for $class.
     */
    protected function inheritance(string $class): array
    {
        return class_parents($class);
    }

    /**
     * Checks if *$class* may be used as an argument to ``getInheritedClassesFor()``.
     *
     * @psalm-assert-if-true class-string $class
     */
    protected function supportsActual(string $class): bool
    {
        return class_exists($class);
    }
}

// vim: syntax=php sw=4 ts=4 et:
