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
     * The labels of the spans of the optional keys, by key.
     *
     * @var array<array-key, non-empty-string>
     */
    private array $optionalKeys;
    private BernoulliDistribution $distribution;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param array<array-key, AbstractGenerator<mixed>> $generators
     * @param list<array-key>                            $optionalKeys
     */
    public function __construct(array $generators, array $optionalKeys = [])
    {
        // an optional key is used as an array key so that a numeric string
        // becomes the integer that PHP uses as the key of the record
        $optional = [];

        foreach ($optionalKeys as $key) {
            $optional[$key] = true;
        }

        $components = [];

        foreach ($generators as $key => $generator) {
            $component = $key . ':' . $generator->label();

            if (isset($optional[$key])) {
                $component .= '?';
            }

            $components[] = $component;
        }

        $this->label = self::labelOf('records', ...$components);

        foreach ($optional as $key => $true) {
            $optional[$key] = self::labelOf('optional', $this->label, (string) $key);
        }

        $this->generators   = $generators;
        $this->optionalKeys = $optional;
        $this->distribution = new BernoulliDistribution(self::PROBABILITY_OF_OPTIONAL_KEY);
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): array
    {
        $record = [];

        foreach ($this->generators as $key => $generator) {
            if (!isset($this->optionalKeys[$key])) {
                $record[$key] = $generator->generate($source);

                continue;
            }

            $source->startSpan($this->optionalKeys[$key]);

            if ($source->draw(1, $this->distribution) === 1) {
                $record[$key] = $generator->generate($source);
            }

            $source->endSpan();
        }

        return $record;
    }
}
