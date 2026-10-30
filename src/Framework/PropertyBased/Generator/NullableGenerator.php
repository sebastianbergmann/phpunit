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
use SebastianBergmann\ChoiceSequence\BernoulliDistribution;

/**
 * Generates null (choice 0) or a value of the given generator (choice 1).
 *
 * @template T
 *
 * @template-extends AbstractGenerator<?T>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class NullableGenerator extends AbstractGenerator
{
    public const float DEFAULT_PROBABILITY_OF_VALUE = 0.8;

    /**
     * @var AbstractGenerator<T>
     */
    private AbstractGenerator $generator;
    private BernoulliDistribution $distribution;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param AbstractGenerator<T> $generator
     */
    public function __construct(AbstractGenerator $generator, float $probabilityOfValue = self::DEFAULT_PROBABILITY_OF_VALUE)
    {
        $this->label = self::labelOf('nullable', $generator->label());

        $this->generator    = $generator;
        $this->distribution = new BernoulliDistribution($probabilityOfValue);
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): mixed
    {
        if ($source->draw(1, $this->distribution) === 0) {
            return null;
        }

        return $this->generator->generate($source);
    }
}
