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
 * @extends AbstractInheritanceConstraint<UsesTrait>
 */
final class UsesTrait extends AbstractInheritanceConstraint
{
    /**
     * @throws InvalidArgumentException
     *
     * @psalm-assert trait-string $trait
     */
    public static function fromTraitString(string $trait): self
    {
        if (!trait_exists($trait)) {
            throw InvalidArgumentException::create(1, 'trait-string');
        }

        return new self($trait);
    }

    /**
     * Returns short description of what we examine, e.g. ``'impements interface'``.
     */
    protected function verb(bool $negated = false): string
    {
        if ($negated) {
            return 'does not use trait';
        }

        return 'uses trait';
    }

    /**
     * Returns an array of traits $class uses.
     */
    protected function inheritance(string $class): array
    {
        return class_uses($class);
    }

    /**
     * Checks if *$class* may be used as an argument to ``getInheritedClassesFor()``.
     *
     * @psalm-assert-if-true class-string|trait-string $class
     */
    protected function supportsActual(string $class): bool
    {
        return class_exists($class) || trait_exists($class);
    }
}

// vim: syntax=php sw=4 ts=4 et:
