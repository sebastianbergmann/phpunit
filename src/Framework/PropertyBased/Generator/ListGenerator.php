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
use function is_object;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;

/**
 * Generates lists of the values of a generator. In a list of unique elements,
 * no element equals another one: an element that does is generated again.
 * Objects are equal when they are of the same class and assertEquals()
 * considers them equal, other values when they are identical.
 *
 * @template T
 *
 * @template-extends AbstractGenerator<list<T>>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ListGenerator extends AbstractGenerator
{
    /**
     * @var AbstractGenerator<T>
     */
    private AbstractGenerator $elements;
    private Repetition $repetition;
    private bool $unique;

    /**
     * @param AbstractGenerator<T> $elements
     * @param non-negative-int     $minimumSize
     * @param ?non-negative-int    $maximumSize
     */
    public function __construct(AbstractGenerator $elements, int $minimumSize, ?int $maximumSize, float $probabilityOfAnotherElement = Repetition::DEFAULT_PROBABILITY_OF_ANOTHER_ELEMENT, bool $unique = false)
    {
        $this->elements   = $elements;
        $this->repetition = new Repetition($minimumSize, $maximumSize, $probabilityOfAnotherElement);
        $this->unique     = $unique;
    }

    protected function generateValue(ChoiceSource $source): array
    {
        $list = [];

        $this->repetition->repeat(
            $source,
            function () use ($source, &$list): bool
            {
                $element = $this->elements->generate($source);

                if ($this->unique && $this->contains($list, $element)) {
                    return false;
                }

                $list[] = $element;

                return true;
            },
        );

        return $list;
    }

    /**
     * @param list<T> $list
     * @param T       $element
     */
    private function contains(array $list, mixed $element): bool
    {
        if (!is_object($element)) {
            return in_array($element, $list, true);
        }

        foreach ($list as $other) {
            if (is_object($other) && $other::class === $element::class && $this->equal($other, $element)) {
                return true;
            }
        }

        return false;
    }

    private function equal(object $a, object $b): bool
    {
        try {
            ComparatorFactory::getInstance()->getComparatorFor($a, $b)->assertEquals($a, $b);
        } catch (ComparisonFailure) {
            return false;
        }

        return true;
    }
}
