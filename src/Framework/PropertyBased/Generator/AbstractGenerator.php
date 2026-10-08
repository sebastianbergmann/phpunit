<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use Closure;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\PropertyBased\ValueGeneratorNotSupportedException;

/**
 * A generator builds a value from the choices it draws from a choice source.
 * It does not implement shrinking: a value is shrunk by shrinking the choice
 * sequence it was built from.
 *
 * @template-covariant T
 *
 * @template-implements ValueGenerator<T>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class AbstractGenerator implements ValueGenerator
{
    /**
     * @template U
     *
     * @param ValueGenerator<U> $generator
     *
     * @throws ValueGeneratorNotSupportedException
     *
     * @return AbstractGenerator<U>
     */
    final public static function from(ValueGenerator $generator): self
    {
        if (!$generator instanceof self) {
            throw ValueGeneratorNotSupportedException::forClass($generator::class);
        }

        return $generator;
    }

    final public function map(Closure $mapper): ValueGenerator
    {
        return new MappedGenerator($this, $mapper);
    }

    final public function filter(Closure $predicate): ValueGenerator
    {
        return new FilteredGenerator($this, $predicate);
    }

    final public function flatMap(Closure $mapper): ValueGenerator
    {
        return new FlatMappedGenerator($this, $mapper);
    }

    /**
     * Generates a value and marks the choices it consumed as a span.
     *
     * @throws ExampleRejectedException
     *
     * @return T
     */
    final public function generate(ChoiceSource $source): mixed
    {
        $source->startSpan();

        $value = $this->generateValue($source);

        $source->endSpan();

        return $value;
    }

    /**
     * @throws ExampleRejectedException
     *
     * @return T
     */
    abstract protected function generateValue(ChoiceSource $source): mixed;
}
