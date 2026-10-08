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
use PHPUnit\Framework\PropertyBased\Distribution\BernoulliDistribution;

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
    private const float PROBABILITY_OF_VALUE = 0.8;

    /**
     * @var AbstractGenerator<T>
     */
    private AbstractGenerator $generator;
    private BernoulliDistribution $distribution;

    /**
     * @param AbstractGenerator<T> $generator
     */
    public function __construct(AbstractGenerator $generator)
    {
        $this->generator    = $generator;
        $this->distribution = new BernoulliDistribution(self::PROBABILITY_OF_VALUE);
    }

    protected function generateValue(ChoiceSource $source): mixed
    {
        if ($source->draw(1, $this->distribution) === 0) {
            return null;
        }

        return $this->generator->generate($source);
    }
}
