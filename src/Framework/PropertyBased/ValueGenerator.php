<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use Closure;

/**
 * Generates values of type T for the parameters of a property.
 *
 * Generators are created with the static methods of Generators and composed
 * with the methods of this interface. Generators that are composed this way
 * shrink just like the ones they are composed of.
 *
 * This interface is not meant to be implemented outside of PHPUnit: an object
 * of any other class that implements it is reported as an error when it is
 * used as a generator.
 *
 * @template-covariant T
 */
interface ValueGenerator
{
    /**
     * Returns a generator for the values that $mapper returns for the values
     * of this generator.
     *
     * @template U
     *
     * @param Closure(T): U $mapper
     *
     * @return ValueGenerator<U>
     */
    public function map(Closure $mapper): self;

    /**
     * Returns a generator for the values of this generator that satisfy
     * $predicate. Values that do not satisfy it are generated again. When
     * this fails too often, the example is rejected.
     *
     * @param Closure(T): bool $predicate
     *
     * @return ValueGenerator<T>
     */
    public function filter(Closure $predicate): self;

    /**
     * Returns a generator for the values of the generators that $mapper
     * returns for the values of this generator.
     *
     * @template U
     *
     * @param Closure(T): ValueGenerator<U> $mapper
     *
     * @return ValueGenerator<U>
     */
    public function flatMap(Closure $mapper): self;
}
