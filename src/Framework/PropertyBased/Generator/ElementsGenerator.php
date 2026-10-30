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

use function count;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\ChoiceSequence\UniformDistribution;

/**
 * Generates one of the given values. The choice is the index of the value,
 * so the first value is the simplest one.
 *
 * @template T
 *
 * @template-extends AbstractGenerator<T>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ElementsGenerator extends AbstractGenerator
{
    /**
     * @var non-empty-list<T>
     */
    private array $values;
    private UniformDistribution $distribution;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param non-empty-list<T> $values
     */
    public function __construct(array $values)
    {
        $this->label = self::labelOf('elements', (string) count($values));

        $this->values       = $values;
        $this->distribution = new UniformDistribution;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): mixed
    {
        return $source->choose($this->values, $this->distribution);
    }
}
