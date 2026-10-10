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
use function array_pop;
use function assert;
use function count;
use function spl_object_id;
use function strtolower;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Distribution\Distribution;
use PHPUnit\Framework\PropertyBased\Generator\DeferredGenerator;
use Random\Randomizer;

/**
 * Provides the choices from which generators build the arguments of an
 * example, and records them as the example's choice sequence.
 *
 * While examples are generated, choices are drawn from a random number
 * generator. When a choice sequence is replayed, the choices are read from
 * it instead: choices beyond its end are 0, and a choice that exceeds the
 * range it is drawn from is clamped to the range.
 *
 * It also provides the deriver for the generators that are derived from types
 * while the example is generated, keeps track of the objects that are being
 * generated, so that recursive construction can be limited, and records how
 * objects were constructed, so that they can be exported.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ChoiceSource
{
    public const int DEFAULT_MAXIMUM_NUMBER_OF_CHOICES = 8192;
    private readonly ?Randomizer $randomizer;

    /**
     * @var list<non-negative-int>
     */
    private readonly array $replayedChoices;

    /**
     * @var positive-int
     */
    private readonly int $maximumNumberOfChoices;

    /**
     * @var list<non-negative-int>
     */
    private array $choices = [];

    /**
     * @var list<Span>
     */
    private array $spans = [];

    /**
     * @var list<non-negative-int>
     */
    private array $openSpans = [];
    private ?GeneratorDeriver $deriver;

    /**
     * @var array<lowercase-string, positive-int>
     */
    private array $objectsBeingGenerated = [];

    /**
     * The number of choices that had been made when the deferred generators
     * that are generating a value started to generate it, by their object ID.
     *
     * @var array<int, non-negative-int>
     */
    private array $deferredGeneratorsBeingGenerated = [];
    private readonly Constructions $constructions;

    /**
     * @param positive-int $maximumNumberOfChoices
     */
    public static function random(Randomizer $randomizer, int $maximumNumberOfChoices = self::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, ?GeneratorDeriver $deriver = null): self
    {
        return new self($randomizer, [], $maximumNumberOfChoices, $deriver);
    }

    /**
     * @param positive-int $maximumNumberOfChoices
     */
    public static function replay(ChoiceSequence $sequence, int $maximumNumberOfChoices = self::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, ?GeneratorDeriver $deriver = null): self
    {
        return new self(null, $sequence->choices(), $maximumNumberOfChoices, $deriver);
    }

    /**
     * @param list<non-negative-int> $replayedChoices
     * @param positive-int           $maximumNumberOfChoices
     */
    private function __construct(?Randomizer $randomizer, array $replayedChoices, int $maximumNumberOfChoices, ?GeneratorDeriver $deriver)
    {
        $this->randomizer             = $randomizer;
        $this->replayedChoices        = $replayedChoices;
        $this->maximumNumberOfChoices = $maximumNumberOfChoices;
        $this->deriver                = $deriver;
        $this->constructions          = new Constructions;
    }

    /**
     * The objects that generators created with new while the example was
     * generated, with the arguments that were passed to their constructors.
     */
    public function constructions(): Constructions
    {
        return $this->constructions;
    }

    /**
     * The deriver for generators that are derived from types while the example
     * is generated. Without one, a deriver without generators registered for
     * types is used.
     */
    public function deriver(): GeneratorDeriver
    {
        if ($this->deriver === null) {
            $this->deriver = new GeneratorDeriver(GeneratorRegistry::empty());
        }

        return $this->deriver;
    }

    /**
     * Records that an object of the class is being generated.
     *
     * @param class-string $className
     *
     * @return positive-int the number of objects of the class that are being generated, including this one
     */
    public function startObject(string $className): int
    {
        $key   = strtolower($className);
        $count = ($this->objectsBeingGenerated[$key] ?? 0) + 1;

        $this->objectsBeingGenerated[$key] = $count;

        return $count;
    }

    /**
     * @param class-string $className
     */
    public function endObject(string $className): void
    {
        $key = strtolower($className);

        assert(isset($this->objectsBeingGenerated[$key]));

        $count = $this->objectsBeingGenerated[$key] - 1;

        if ($count === 0) {
            unset($this->objectsBeingGenerated[$key]);

            return;
        }

        $this->objectsBeingGenerated[$key] = $count;
    }

    /**
     * Records that the deferred generator starts to generate a value, and
     * returns the number of choices that had been made when it started to
     * generate the value that this one is part of, or null when it is not
     * generating another value. When that is the number of choices that have
     * been made so far, the deferred generator refers to itself before a
     * choice is made.
     *
     * @param DeferredGenerator<mixed> $generator
     *
     * @return ?non-negative-int to be passed to endDeferred()
     */
    public function startDeferred(DeferredGenerator $generator): ?int
    {
        $id     = spl_object_id($generator);
        $before = $this->deferredGeneratorsBeingGenerated[$id] ?? null;

        $this->deferredGeneratorsBeingGenerated[$id] = count($this->choices);

        return $before;
    }

    /**
     * @param DeferredGenerator<mixed> $generator
     * @param ?non-negative-int        $before    what startDeferred() returned
     */
    public function endDeferred(DeferredGenerator $generator, ?int $before): void
    {
        $id = spl_object_id($generator);

        if ($before === null) {
            unset($this->deferredGeneratorsBeingGenerated[$id]);

            return;
        }

        $this->deferredGeneratorsBeingGenerated[$id] = $before;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfChoices(): int
    {
        return count($this->choices);
    }

    /**
     * Draws a choice from the range [0, $maximum], where 0 represents the
     * simplest option.
     *
     * @param non-negative-int $maximum
     *
     * @throws ExampleRejectedException
     *
     * @return non-negative-int
     */
    public function draw(int $maximum, Distribution $distribution): int
    {
        $index = count($this->choices);

        if ($index >= $this->maximumNumberOfChoices) {
            throw ExampleRejectedException::tooManyChoices($this->maximumNumberOfChoices);
        }

        if ($this->randomizer !== null) {
            $choice = $distribution->sample($this->randomizer, $maximum);
        } else {
            $choice = $this->replayedChoices[$index] ?? 0;

            if ($choice > $maximum) {
                $choice = $maximum;
            }
        }

        $this->choices[] = $choice;

        return $choice;
    }

    /**
     * Draws the index of one of the elements, so that the first element is
     * the simplest one, and returns the element.
     *
     * @template T
     *
     * @param non-empty-list<T> $elements
     *
     * @throws ExampleRejectedException
     *
     * @return T
     */
    public function choose(array $elements, Distribution $distribution): mixed
    {
        $index = $this->draw(count($elements) - 1, $distribution);

        assert(array_key_exists($index, $elements));

        return $elements[$index];
    }

    /**
     * Returns, while choices are drawn at random, one of the candidates with
     * the given probability, and null otherwise.
     *
     * No choice is drawn: a generator uses the candidate only to decide which
     * choices it draws next, with an ExactDistribution, and not to interpret
     * them. This affects the distribution of the generated values, but not
     * the values that a choice sequence is replayed as, nor shrinking.
     *
     * @template T
     *
     * @param non-empty-list<T> $candidates
     *
     * @return ?T
     */
    public function suggest(array $candidates, float $probability): mixed
    {
        if ($this->randomizer === null) {
            return null;
        }

        if ($this->randomizer->nextFloat() >= $probability) {
            return null;
        }

        $index = $this->randomizer->getInt(0, count($candidates) - 1);

        assert(array_key_exists($index, $candidates));

        return $candidates[$index];
    }

    /**
     * Marks the start of a span: the choices that are drawn until the span is
     * ended form one part of the value, for instance an element of a list.
     */
    public function startSpan(): void
    {
        $this->openSpans[] = count($this->choices);
    }

    public function endSpan(): void
    {
        $start = array_pop($this->openSpans);

        assert($start !== null);

        $end = count($this->choices);

        if ($end > $start) {
            $this->spans[] = new Span($start, $end);
        }
    }

    public function sequence(): ChoiceSequence
    {
        return new ChoiceSequence($this->choices, $this->spans);
    }
}
