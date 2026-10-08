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

/**
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

    /**
     * @param AbstractGenerator<T> $elements
     * @param non-negative-int     $minimumSize
     * @param ?non-negative-int    $maximumSize
     */
    public function __construct(AbstractGenerator $elements, int $minimumSize, ?int $maximumSize, float $probabilityOfAnotherElement = Repetition::DEFAULT_PROBABILITY_OF_ANOTHER_ELEMENT)
    {
        $this->elements   = $elements;
        $this->repetition = new Repetition($minimumSize, $maximumSize, $probabilityOfAnotherElement);
    }

    protected function generateValue(ChoiceSource $source): array
    {
        $list = [];

        $this->repetition->repeat(
            $source,
            function () use ($source, &$list): bool
            {
                $list[] = $this->elements->generate($source);

                return true;
            },
        );

        return $list;
    }
}
