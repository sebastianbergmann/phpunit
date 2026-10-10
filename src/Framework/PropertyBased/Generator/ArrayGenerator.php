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

use function array_key_exists;
use function array_key_first;
use function get_debug_type;
use function is_int;
use function is_string;
use function sprintf;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\InvalidGeneratorArgumentException;

/**
 * Generates arrays with keys and values of the given generators. A key that
 * is already present in the array is not added again.
 *
 * @template K of array-key
 * @template V
 *
 * @template-extends AbstractGenerator<array<K, V>>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ArrayGenerator extends AbstractGenerator
{
    /**
     * @var AbstractGenerator<K>
     */
    private AbstractGenerator $keys;

    /**
     * @var AbstractGenerator<V>
     */
    private AbstractGenerator $values;
    private Repetition $repetition;

    /**
     * @param AbstractGenerator<K> $keys
     * @param AbstractGenerator<V> $values
     * @param non-negative-int     $minimumSize
     * @param ?non-negative-int    $maximumSize
     */
    public function __construct(AbstractGenerator $keys, AbstractGenerator $values, int $minimumSize, ?int $maximumSize, float $probabilityOfAnotherElement = Repetition::DEFAULT_PROBABILITY_OF_ANOTHER_ELEMENT)
    {
        $this->keys       = $keys;
        $this->values     = $values;
        $this->repetition = new Repetition($minimumSize, $maximumSize, $probabilityOfAnotherElement);
    }

    protected function generateValue(ChoiceSource $source): array
    {
        $array = [];

        $this->repetition->repeat(
            $source,
            function () use ($source, &$array): bool
            {
                $key = $this->keys->generate($source);

                if (!is_int($key) && !is_string($key)) {
                    throw new InvalidGeneratorArgumentException(
                        sprintf(
                            'The keys of an array must be integers or strings, the generator for the keys generated %s',
                            get_debug_type($key),
                        ),
                    );
                }

                // a string that PHP stores as an integer key, such as '1',
                // is not used, so that the keys of the array are of the type
                // that the generator for the keys generates
                if (array_key_exists($key, $array) || (is_string($key) && is_int(array_key_first([$key => true])))) {
                    return false;
                }

                $array[$key] = $this->values->generate($source);

                return true;
            },
        );

        return $array;
    }
}
