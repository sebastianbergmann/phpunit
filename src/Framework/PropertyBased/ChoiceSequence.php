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

use function array_key_exists;

/**
 * The choices from which the arguments of an example were generated, together
 * with the spans that generators marked while they consumed them, and, when
 * the choices were drawn by a choice source, the largest choice that each
 * choice could have been.
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
     * @var list<non-negative-int>
     */
    private array $maxima;

    /**
     * @param list<non-negative-int> $choices
     * @param list<Span>             $spans
     * @param list<non-negative-int> $maxima  the largest choice that each choice could have been, empty when this is not known
     */
    public function __construct(array $choices, array $spans = [], array $maxima = [])
    {
        $this->choices = $choices;
        $this->spans   = $spans;
        $this->maxima  = $maxima;
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

    /**
     * The largest choice that the choice at the index could have been, null
     * when this is not known.
     *
     * @param non-negative-int $index
     *
     * @return ?non-negative-int
     */
    public function maximumAt(int $index): ?int
    {
        if (!array_key_exists($index, $this->maxima)) {
            return null;
        }

        return $this->maxima[$index];
    }

    /**
     * Choice sequences are ordered shortlex: a shorter sequence is simpler
     * than a longer one, and among sequences of equal length the
     * lexicographically smaller one is simpler.
     */
    public function isSimplerThan(self $other): bool
    {
        // PHP considers an array with fewer elements to be smaller, and
        // compares lists with the same number of elements element by element
        return $this->choices < $other->choices;
    }
}
