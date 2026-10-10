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

use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\Distribution;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;

/**
 * Generates a value of one of the given generators. The first choice is the
 * index of the generator, so the first generator is the simplest one.
 *
 * @template T
 *
 * @template-extends AbstractGenerator<T>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class OneOfGenerator extends AbstractGenerator
{
    /**
     * @var non-empty-list<AbstractGenerator<T>>
     */
    private array $generators;
    private Distribution $distribution;

    /**
     * @param non-empty-list<AbstractGenerator<T>> $generators
     * @param ?Distribution                        $distribution how one of the generators is chosen, uniformly when it is null
     */
    public function __construct(array $generators, ?Distribution $distribution = null)
    {
        if ($distribution === null) {
            $distribution = new UniformDistribution;
        }

        $this->generators   = $generators;
        $this->distribution = $distribution;
    }

    protected function generateValue(ChoiceSource $source): mixed
    {
        return $source->choose($this->generators, $this->distribution)->generate($source);
    }
}
