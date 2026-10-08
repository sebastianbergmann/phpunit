<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

/**
 * The choices from which the arguments of an example were generated, together
 * with the spans that generators marked while they consumed them.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ChoiceSequence
{
    /**
     * @var list<non-negative-int>
     */
    private array $choices;

    /**
     * @var list<Span>
     */
    private array $spans;

    /**
     * @param list<non-negative-int> $choices
     * @param list<Span>             $spans
     */
    public function __construct(array $choices, array $spans = [])
    {
        $this->choices = $choices;
        $this->spans   = $spans;
    }

    /**
     * @return list<non-negative-int>
     */
    public function choices(): array
    {
        return $this->choices;
    }

    /**
     * @return list<Span>
     */
    public function spans(): array
    {
        return $this->spans;
    }
}
