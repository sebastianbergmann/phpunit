<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\RegularExpression;

use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Generator\Repetition;

/**
 * A part of a regular expression with a quantifier, which is repeated from
 * its minimum to its maximum number of times, the minimum being the simplest.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class QuantifiedNode implements Node
{
    private Node $node;
    private Repetition $repetition;

    /**
     * @param non-negative-int  $minimum
     * @param ?non-negative-int $maximum null when the number of repetitions is unbounded
     */
    public function __construct(Node $node, int $minimum, ?int $maximum)
    {
        $this->node       = $node;
        $this->repetition = new Repetition($minimum, $maximum);
    }

    public function generate(ChoiceSource $source): string
    {
        $string = '';

        $this->repetition->repeat(
            $source,
            function () use ($source, &$string): bool
            {
                $string .= $this->node->generate($source);

                return true;
            },
        );

        return $string;
    }
}
