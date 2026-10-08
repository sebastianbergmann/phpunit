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

use function get_debug_type;
use function sprintf;
use Closure;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\InvalidGeneratorArgumentException;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

/**
 * Generates a value of the given generator, and then a value of the generator
 * that the mapper returns for it.
 *
 * @template T
 * @template U
 *
 * @template-extends AbstractGenerator<U>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FlatMappedGenerator extends AbstractGenerator
{
    /**
     * @var AbstractGenerator<T>
     */
    private AbstractGenerator $generator;

    /**
     * @var Closure(T): ValueGenerator<U>
     */
    private Closure $mapper;

    /**
     * @param AbstractGenerator<T>          $generator
     * @param Closure(T): ValueGenerator<U> $mapper
     */
    public function __construct(AbstractGenerator $generator, Closure $mapper)
    {
        $this->generator = $generator;
        $this->mapper    = $mapper;
    }

    protected function generateValue(ChoiceSource $source): mixed
    {
        $generator = ($this->mapper)($this->generator->generate($source));

        if (!$generator instanceof ValueGenerator) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The closure passed to flatMap() must return a %s, %s was returned',
                    ValueGenerator::class,
                    get_debug_type($generator),
                ),
            );
        }

        return AbstractGenerator::from($generator)->generate($source);
    }
}
