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

use function assert;
use function spl_object_id;
use function strtolower;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Generator\DeferredGenerator;
use Random\Randomizer;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;
use SebastianBergmann\ChoiceSequence\ChoiceSource as Choices;
use SebastianBergmann\ChoiceSequence\ChoiceTree;
use SebastianBergmann\ChoiceSequence\Distribution;
use SebastianBergmann\ChoiceSequence\TooManyChoicesException;

/**
 * Provides the choices from which generators build the arguments of an
 * example, and records them as the example's choice sequence.
 *
 * Drawing the choices, at random or by replaying a choice sequence, and
 * avoiding examples that were generated before with a choice tree is left to
 * the choice source of sebastian/choice-sequence, so that the choices of an
 * example are drawn and replayed in the same way wherever that library is
 * used.
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
    public const int DEFAULT_MAXIMUM_NUMBER_OF_CHOICES = Choices::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES;
    private readonly Choices $choices;
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
    public static function random(Randomizer $randomizer, int $maximumNumberOfChoices = self::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, ?GeneratorDeriver $deriver = null, ?ChoiceTree $tree = null): self
    {
        return new self(Choices::random($randomizer, $maximumNumberOfChoices, $tree), $deriver);
    }

    /**
     * @param positive-int $maximumNumberOfChoices
     */
    public static function replay(ChoiceSequence $sequence, int $maximumNumberOfChoices = self::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, ?GeneratorDeriver $deriver = null, ?ChoiceTree $tree = null): self
    {
        return new self(Choices::replay($sequence, $maximumNumberOfChoices, $tree), $deriver);
    }

    private function __construct(Choices $choices, ?GeneratorDeriver $deriver)
    {
        $this->choices       = $choices;
        $this->deriver       = $deriver;
        $this->constructions = new Constructions;
    }

    /**
     * Records in the choice tree that the example whose choices have been
     * drawn was generated, or rejected while it was generated, so that it is
     * not generated again.
     */
    public function exampleGenerated(): void
    {
        $this->choices->exampleGenerated();
    }

    /**
     * Whether a choice was drawn from another range than when the same choices
     * were drawn before it, which the choice tree tells: the generators do
     * not generate the same values from the same choices, because a value
     * depends on something else, a random number or the time, for instance.
     */
    public function drewDifferentlyThanBefore(): bool
    {
        return $this->choices->drewDifferentlyThanBefore();
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

        $this->deferredGeneratorsBeingGenerated[$id] = $this->choices->numberOfChoices();

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
        return $this->choices->numberOfChoices();
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
        try {
            return $this->choices->draw($maximum, $distribution);
        } catch (TooManyChoicesException $e) {
            throw ExampleRejectedException::tooManyChoices($e->maximumNumberOfChoices());
        }
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
        try {
            return $this->choices->choose($elements, $distribution);
        } catch (TooManyChoicesException $e) {
            throw ExampleRejectedException::tooManyChoices($e->maximumNumberOfChoices());
        }
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
        return $this->choices->suggest($candidates, $probability);
    }

    /**
     * Marks the start of a span: the choices that are drawn until the span is
     * ended form one part of the value, for instance an element of a list.
     *
     * @param non-empty-string $label identifies how the choices of the span are drawn (see AbstractGenerator::label())
     */
    public function startSpan(string $label): void
    {
        $this->choices->startSpan($label);
    }

    public function endSpan(): void
    {
        $this->choices->endSpan();
    }

    /**
     * The choices that have been drawn, with the spans that were ended and
     * contain at least one choice, and the largest choice that each choice
     * could have been. The parent of a span is the closest of these spans
     * that it is part of.
     */
    public function sequence(): ChoiceSequence
    {
        return $this->choices->sequence();
    }
}
