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

use Closure;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\BernoulliDistribution;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;

/**
 * Draws the elements of a collection: first the elements that its minimum
 * size requires, then, as long as its maximum size permits, a choice whether
 * to add another element (1) or to stop (0), followed by that element.
 *
 * Each element, together with the choice that added it, is marked as a span,
 * so that deleting the span from the choice sequence deletes the element.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Repetition
{
    private const float PROBABILITY_OF_ANOTHER_ELEMENT     = 0.8;
    private const int MAXIMUM_NUMBER_OF_DUPLICATE_ELEMENTS = 10;

    /**
     * @var non-negative-int
     */
    private int $minimumSize;

    /**
     * @var ?non-negative-int
     */
    private ?int $maximumSize;
    private BernoulliDistribution $anotherElement;

    /**
     * @param non-negative-int  $minimumSize
     * @param ?non-negative-int $maximumSize
     */
    public function __construct(int $minimumSize, ?int $maximumSize)
    {
        $this->minimumSize    = $minimumSize;
        $this->maximumSize    = $maximumSize;
        $this->anotherElement = new BernoulliDistribution(self::PROBABILITY_OF_ANOTHER_ELEMENT);
    }

    /**
     * @param Closure(): bool $element draws an element and returns whether it
     *                                 was added to the collection, which it is
     *                                 not when it duplicates an earlier element
     *
     * @throws ExampleRejectedException
     */
    public function repeat(ChoiceSource $source, Closure $element): void
    {
        $size       = 0;
        $duplicates = 0;

        while ($this->maximumSize === null || $size < $this->maximumSize) {
            $source->startSpan();

            if ($size >= $this->minimumSize && $source->draw(1, $this->anotherElement) === 0) {
                $source->endSpan();

                return;
            }

            $added = $element();

            $source->endSpan();

            if ($added) {
                $size++;

                $duplicates = 0;

                continue;
            }

            $duplicates++;

            if ($duplicates < self::MAXIMUM_NUMBER_OF_DUPLICATE_ELEMENTS) {
                continue;
            }

            if ($size < $this->minimumSize) {
                throw ExampleRejectedException::tooManyDuplicateElements(self::MAXIMUM_NUMBER_OF_DUPLICATE_ELEMENTS);
            }

            return;
        }
    }
}
