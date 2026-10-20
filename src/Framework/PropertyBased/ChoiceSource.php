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
use function end;
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
 * With a choice tree, the choices are also recorded in the tree, and a choice
 * that is drawn at random and would lead to an example that was generated
 * before is replaced with one that does not (see ChoiceTree).
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

    /**
     * How often a choice that would lead to an example that was generated
     * before is drawn again before the next choice that does not is used.
     */
    private const int NUMBER_OF_ATTEMPTS_TO_DRAW_AN_UNEXHAUSTED_CHOICE = 3;
    private readonly ?Randomizer $randomizer;
    private readonly ?ChoiceTree $tree;

    /**
     * The node of the choice tree for the choices that have been drawn, null
     * when there is no choice tree or it is no longer used.
     */
    private ?ChoiceTreeNode $node;

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
     * The spans that were started, in the order in which they were started.
     * The end of a span is null until it is ended.
     *
     * @var list<array{start: non-negative-int, end: ?non-negative-int, label: non-empty-string}>
     */
    private array $spans = [];

    /**
     * The indexes of the spans that were started and not yet ended.
     *
     * @var list<non-negative-int>
     */
    private array $openSpans = [];
    private ?GeneratorDeriver $deriver;

    /**
     * Whether a choice was drawn from another range than when the same
     * choices were drawn before it, see drewDifferentlyThanBefore().
     */
    private bool $drewDifferentlyThanBefore = false;

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
        return new self($randomizer, [], $maximumNumberOfChoices, $deriver, $tree);
    }

    /**
     * @param positive-int $maximumNumberOfChoices
     */
    public static function replay(ChoiceSequence $sequence, int $maximumNumberOfChoices = self::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, ?GeneratorDeriver $deriver = null, ?ChoiceTree $tree = null): self
    {
        return new self(null, $sequence->choices(), $maximumNumberOfChoices, $deriver, $tree);
    }

    /**
     * @param list<non-negative-int> $replayedChoices
     * @param positive-int           $maximumNumberOfChoices
     */
    private function __construct(?Randomizer $randomizer, array $replayedChoices, int $maximumNumberOfChoices, ?GeneratorDeriver $deriver, ?ChoiceTree $tree)
    {
        $this->randomizer             = $randomizer;
        $this->replayedChoices        = $replayedChoices;
        $this->maximumNumberOfChoices = $maximumNumberOfChoices;
        $this->deriver                = $deriver;
        $this->tree                   = $tree;
        $this->node                   = null;
        $this->constructions          = new Constructions;

        // when every example has been generated, an example that was
        // generated before cannot be avoided
        if ($tree !== null && !$tree->isExhausted()) {
            $this->node = $tree->root();
        }
    }

    /**
     * Records in the choice tree that the example whose choices have been
     * drawn was generated, or rejected while it was generated, so that it is
     * not generated again.
     */
    public function exampleGenerated(): void
    {
        $this->node?->exhaust();

        $this->node = null;
    }

    /**
     * Whether a choice was drawn from another range than when the same choices
     * were drawn before it, which the choice tree tells: the generators do
     * not generate the same values from the same choices, because a value
     * depends on something else, a random number or the time, for instance.
     */
    public function drewDifferentlyThanBefore(): bool
    {
        return $this->drewDifferentlyThanBefore;
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

        if ($this->node !== null && !$this->node->draws($maximum)) {
            // the generators do not draw the same choices as before
            $this->tree?->discard();

            $this->node                      = null;
            $this->drewDifferentlyThanBefore = true;
        }

        if ($this->randomizer !== null) {
            $choice = $this->randomChoice($maximum, $distribution);
        } else {
            $choice = $this->replayedChoices[$index] ?? 0;

            if ($choice > $maximum) {
                $choice = $maximum;
            }
        }

        if ($this->node !== null) {
            assert($this->tree !== null);

            $this->node = $this->tree->child($this->node, $choice);
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
     *
     * @param non-empty-string $label identifies how the choices of the span are drawn (see AbstractGenerator::label())
     */
    public function startSpan(string $label): void
    {
        $this->openSpans[] = count($this->spans);

        $this->spans[] = [
            'start' => count($this->choices),
            'end'   => null,
            'label' => $label,
        ];
    }

    public function endSpan(): void
    {
        $index = array_pop($this->openSpans);

        assert($index !== null);

        $this->spans[$index]['end'] = count($this->choices);
    }

    /**
     * The choices that have been drawn, with the spans that were ended and
     * contain at least one choice. The parent of a span is the closest of
     * these spans that it is part of.
     */
    public function sequence(): ChoiceSequence
    {
        $spans = [];

        // the ends and the indexes of the spans that the next span can be
        // part of, the innermost one last: spans are ended in the reverse
        // order in which they are started, so they are either part of each
        // other or do not overlap
        $enclosingEnds    = [];
        $enclosingIndexes = [];

        foreach ($this->spans as $span) {
            $start = $span['start'];
            $end   = $span['end'];

            if ($end === null || $end <= $start) {
                continue;
            }

            while ($enclosingEnds !== [] && end($enclosingEnds) <= $start) {
                array_pop($enclosingEnds);
                array_pop($enclosingIndexes);
            }

            $parent = null;

            if ($enclosingIndexes !== []) {
                $parent = end($enclosingIndexes);
            }

            $enclosingEnds[]    = $end;
            $enclosingIndexes[] = count($spans);

            $spans[] = new Span($start, $end, $span['label'], $parent);
        }

        return new ChoiceSequence($this->choices, $spans);
    }

    /**
     * Draws a choice at random that does not lead to an example that was
     * generated before: it is drawn again a few times, and then the next
     * choice that does not is used.
     *
     * @param non-negative-int $maximum
     *
     * @return non-negative-int
     */
    private function randomChoice(int $maximum, Distribution $distribution): int
    {
        assert($this->randomizer !== null);

        $choice = $distribution->sample($this->randomizer, $maximum);

        if ($this->node === null) {
            return $choice;
        }

        for ($attempt = 1; $attempt < self::NUMBER_OF_ATTEMPTS_TO_DRAW_AN_UNEXHAUSTED_CHOICE && $this->node->leadsToExhaustedNode($choice); $attempt++) {
            $choice = $distribution->sample($this->randomizer, $maximum);
        }

        return $this->node->unexhaustedChoice($choice);
    }
}
