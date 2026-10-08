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

use function in_array;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\BernoulliDistribution;

/**
 * Generates arrays with the given keys and a value of the respective generator
 * for each of them.
 *
 * For an optional key, a choice is drawn first whether to omit the key (0) or
 * to add it (1). The choice and the value are marked as a span.
 *
 * @template-extends AbstractGenerator<array<array-key, mixed>>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RecordGenerator extends AbstractGenerator
{
    private const float PROBABILITY_OF_OPTIONAL_KEY = 0.5;

    /**
     * @var array<array-key, AbstractGenerator<mixed>>
     */
    private array $generators;

    /**
     * @var list<array-key>
     */
    private array $optionalKeys;
    private BernoulliDistribution $distribution;

    /**
     * @param array<array-key, AbstractGenerator<mixed>> $generators
     * @param list<array-key>                            $optionalKeys
     */
    public function __construct(array $generators, array $optionalKeys = [])
    {
        $this->generators   = $generators;
        $this->optionalKeys = $optionalKeys;
        $this->distribution = new BernoulliDistribution(self::PROBABILITY_OF_OPTIONAL_KEY);
    }

    protected function generateValue(ChoiceSource $source): array
    {
        $record = [];

        foreach ($this->generators as $key => $generator) {
            if (!in_array($key, $this->optionalKeys, true)) {
                $record[$key] = $generator->generate($source);

                continue;
            }

            $source->startSpan();

            if ($source->draw(1, $this->distribution) === 1) {
                $record[$key] = $generator->generate($source);
            }

            $source->endSpan();
        }

        return $record;
    }
}
