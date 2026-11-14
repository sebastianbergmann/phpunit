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
use function is_bool;
use function sprintf;
use Closure;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\InvalidGeneratorArgumentException;

/**
 * Generates values of the given generator until one satisfies the predicate.
 * The choices of the values that did not satisfy it remain in the choice
 * sequence, from which shrinking can delete them.
 *
 * @template T
 *
 * @template-extends AbstractGenerator<T>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FilteredGenerator extends AbstractGenerator
{
    private const int MAXIMUM_NUMBER_OF_ATTEMPTS = 3;

    /**
     * @var AbstractGenerator<T>
     */
    private AbstractGenerator $generator;

    /**
     * @var Closure(T): bool
     */
    private Closure $predicate;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param AbstractGenerator<T> $generator
     * @param Closure(T): bool     $predicate
     */
    public function __construct(AbstractGenerator $generator, Closure $predicate)
    {
        $this->label = self::labelOf('filter', $generator->label());

        $this->generator = $generator;
        $this->predicate = $predicate;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): mixed
    {
        for ($attempt = 1; $attempt <= self::MAXIMUM_NUMBER_OF_ATTEMPTS; $attempt++) {
            $value     = $this->generator->generate($source);
            $satisfied = ($this->predicate)($value);

            if (!is_bool($satisfied)) {
                throw new InvalidGeneratorArgumentException(
                    sprintf(
                        'The closure passed to filter() must return a bool, %s was returned',
                        get_debug_type($satisfied),
                    ),
                );
            }

            if ($satisfied) {
                return $value;
            }
        }

        throw ExampleRejectedException::filterNotSatisfied(self::MAXIMUM_NUMBER_OF_ATTEMPTS);
    }
}
