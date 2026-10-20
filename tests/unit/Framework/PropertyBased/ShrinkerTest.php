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

use const PHP_INT_MAX;
use function array_count_values;
use function array_filter;
use function array_search;
use function array_sum;
use function array_unique;
use function array_values;
use function array_walk_recursive;
use function count;
use function implode;
use function is_array;
use function max;
use function mb_strlen;
use function strlen;
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

#[CoversClass(Shrinker::class)]
#[CoversClass(ShrinkingResult::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ShrinkerTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ValueGenerator<mixed>, Closure(mixed): bool}>
     */
    public static function counterexampleProvider(): array
    {
        return [
            'integer at a threshold' => [
                1234,
                Generators::integers(0, 1000000),
                static fn (int $value): bool => $value >= 1234,
            ],

            'negative integer closest to zero' => [
                -1001,
                Generators::integers(),
                static fn (int $value): bool => $value < -1000,
            ],

            'list with the smallest sum' => [
                [101],
                Generators::lists(Generators::integers(0, 1000)),
                static fn (array $value): bool => array_sum($value) > 100,
            ],

            'list with the fewest and simplest elements' => [
                [0, 0, 0],
                Generators::lists(Generators::integers()),
                static fn (array $value): bool => count($value) >= 3,
            ],

            'string with the first character that is not ASCII' => [
                "\u{80}",
                Generators::strings(),
                static fn (string $value): bool => strlen($value) !== mb_strlen($value, 'UTF-8'),
            ],

            'integral float' => [
                2.0,
                Generators::floats(),
                static fn (float $value): bool => $value > 1.0,
            ],

            'tuple with the simplest components' => [
                [0, 11],
                Generators::tuples(Generators::integers(0, 100), Generators::integers(0, 100)),
                static fn (array $value): bool => $value[1] > 10,
            ],

            'tuple with an amount moved from the first to the second component' => [
                [0, 11],
                Generators::tuples(Generators::integers(0, 100), Generators::integers(0, 100)),
                static fn (array $value): bool => $value[0] + $value[1] > 10,
            ],

            'tuple with the largest amount moved that keeps the property falsified' => [
                [5, 15],
                Generators::tuples(Generators::integers(0, 100), Generators::integers(0, 100)),
                static fn (array $value): bool => $value[0] + $value[1] >= 20 && $value[1] <= 15,
            ],

            'tuple of integers that are drawn as two choices each' => [
                [0, 11],
                Generators::tuples(Generators::integers(), Generators::integers()),
                static fn (array $value): bool => $value[0] + $value[1] > 10 && $value[0] >= 0 && $value[1] >= 0,
            ],

            'tuple of integers whose product is at least a threshold' => [
                [10, 100],
                Generators::tuples(Generators::integers(0, 100), Generators::integers(0, 100)),
                static fn (array $value): bool => $value[0] * $value[1] >= 1000,
            ],

            'list whose size an earlier value determines, with an index into it' => [
                [[0, 0], 1],
                Generators::integers(1, 10)->flatMap(
                    static fn (int $size): ValueGenerator => Generators::tuples(
                        Generators::lists(Generators::integers(0, 9), $size, $size),
                        Generators::integers(0, $size - 1),
                    ),
                ),
                static fn (array $value): bool => array_search($value[0][$value[1]], $value[0], true) !== $value[1],
            ],

            'list whose size an earlier value determines, with collections between them and an index into it' => [
                ['aa', 'cc', [0, 0], 1],
                Generators::integers(1, 10)->flatMap(
                    static fn (int $size): ValueGenerator => Generators::tuples(
                        Generators::strings('ab', 2, 5),
                        Generators::strings('cd', 2, 5),
                        Generators::lists(Generators::integers(0, 9), $size, $size),
                        Generators::integers(0, $size - 1),
                    ),
                ),
                static fn (array $value): bool => array_search($value[2][$value[3]], $value[2], true) !== $value[3],
            ],

            'list with two equal elements' => [
                [0, 0],
                Generators::lists(Generators::integers()),
                static fn (array $value): bool => count($value) !== count(array_unique($value)),
            ],

            'list with an element that occurs three times' => [
                [0, 0, 0],
                Generators::lists(Generators::integers(-100, 100)),
                static fn (array $value): bool => $value !== [] && max(array_count_values($value)) >= 3,
            ],

            'list with two equal elements that are not small' => [
                [50, 50],
                Generators::lists(Generators::integers(0, 100)),
                static function (array $value): bool
                {
                    $large = array_values(array_filter($value, static fn (int $element): bool => $element >= 50));

                    return count($large) !== count(array_unique($large));
                },
            ],

            'mapped value' => [
                102,
                Generators::integers(0, 1000)->map(static fn (int $value): int => $value * 2),
                static fn (int $value): bool => $value > 100,
            ],

            'filtered value' => [
                102,
                Generators::integers(0, 1000)->filter(static fn (int $value): bool => $value % 2 === 0),
                static fn (int $value): bool => $value > 100,
            ],

            'flat-mapped value' => [
                [5],
                Generators::integers(1, 5)->flatMap(static fn (int $size): ValueGenerator => Generators::lists(Generators::integers(0, 9), $size, $size)),
                static fn (array $value): bool => max($value) >= 5,
            ],

            'recursive value' => [
                50,
                Generators::recursive(
                    Generators::integers(0, 100),
                    static fn (ValueGenerator $values): ValueGenerator => Generators::lists($values),
                ),
                static function (mixed $value): bool
                {
                    $integers = [$value];

                    if (is_array($value)) {
                        $integers = [];

                        array_walk_recursive(
                            $value,
                            static function (int $integer) use (&$integers): void
                            {
                                $integers[] = $integer;
                            },
                        );
                    }

                    return $integers !== [] && max($integers) >= 50;
                },
            ],

            'tree that a deferred generator generates' => [
                50,
                self::trees(),
                static function (mixed $value): bool
                {
                    $integers = [$value];

                    if (is_array($value)) {
                        $integers = [];

                        array_walk_recursive(
                            $value,
                            static function (int $integer) use (&$integers): void
                            {
                                $integers[] = $integer;
                            },
                        );
                    }

                    return max($integers) >= 50;
                },
            ],

            'value that is falsified by every example' => [
                [],
                Generators::lists(Generators::strings()),
                static fn (array $value): bool => true,
            ],
        ];
    }

    /**
     * @param ValueGenerator<mixed> $generator
     * @param Closure(mixed): bool  $falsifies
     */
    #[DataProvider('counterexampleProvider')]
    public function testShrinksACounterexampleToTheMinimalCounterexample(mixed $expected, ValueGenerator $generator, Closure $falsifies): void
    {
        $result = $this->shrinker($generator, $falsifies)->shrink($this->falsifyingSequence($generator, $falsifies));

        $this->assertSame($expected, $this->replay($generator, $result->sequence()));
        $this->assertFalse($result->budgetExhausted());
    }

    public function testDoesNotChangeACounterexampleForWhichNoSimplerCounterexampleExists(): void
    {
        $generator = Generators::integers(0, 1000);
        $falsifies = static fn (int $value): bool => $value === 777;

        $result = $this->shrinker($generator, $falsifies)->shrink(new ChoiceSequence([777]));

        $this->assertSame([777], $result->sequence()->choices());
        $this->assertSame(0, $result->numberOfSteps());
        $this->assertGreaterThan(0, $result->numberOfAttempts());
    }

    public function testCountsTheAttemptsAndTheStepsThatMadeTheCounterexampleSimpler(): void
    {
        $attempts = 0;

        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate) use (&$attempts): ?ChoiceSequence
            {
                $attempts++;

                if ($candidate->choices() === [0]) {
                    return $candidate;
                }

                return null;
            },
            1000,
        );

        $result = $shrinker->shrink(new ChoiceSequence([5]));

        $this->assertSame([0], $result->sequence()->choices());
        $this->assertSame(1, $result->numberOfSteps());
        $this->assertSame($attempts, $result->numberOfAttempts());
    }

    public function testStopsWhenTheMaximumNumberOfAttemptsHasBeenMade(): void
    {
        $generator = Generators::lists(Generators::integers(0, 1000));
        $falsifies = static fn (array $value): bool => array_sum($value) > 100;

        $original = $this->falsifyingSequence($generator, $falsifies);
        $result   = $this->shrinker($generator, $falsifies, 3)->shrink($original);

        $this->assertSame(3, $result->numberOfAttempts());
        $this->assertTrue($result->budgetExhausted());
        $this->assertFalse($result->interrupted());
        $this->assertFalse($original->isSimplerThan($result->sequence()));
    }

    public function testDoesNotShrinkWhenNoAttemptMayBeMade(): void
    {
        $shrinker = new Shrinker(
            function (ChoiceSequence $candidate): ?ChoiceSequence
            {
                $this->fail('No candidate is to be replayed');
            },
            0,
        );

        $result = $shrinker->shrink(new ChoiceSequence([3, 9, 27, 81]));

        $this->assertSame([3, 9, 27, 81], $result->sequence()->choices());
        $this->assertSame(0, $result->numberOfAttempts());
        $this->assertSame(0, $result->numberOfSteps());
        $this->assertFalse($result->budgetExhausted());
        $this->assertFalse($result->interrupted());
    }

    public function testStopsWithoutCountingFurtherAttemptsWhenItIsInterrupted(): void
    {
        $attempts = 0;

        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate) use (&$attempts): ?ChoiceSequence
            {
                $attempts++;

                return null;
            },
            1000,
            static function () use (&$attempts): bool
            {
                return $attempts === 2;
            },
        );

        $result = $shrinker->shrink(new ChoiceSequence([3, 9, 27, 81], [new Span(0, 2, 'element', null), new Span(2, 4, 'element', null)]));

        $this->assertSame(2, $attempts);
        $this->assertSame(2, $result->numberOfAttempts());
        $this->assertTrue($result->interrupted());
        $this->assertFalse($result->budgetExhausted());
        $this->assertSame([3, 9, 27, 81], $result->sequence()->choices());
    }

    public function testDoesNotReplayTheSameCandidateTwice(): void
    {
        $candidates = [];

        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate) use (&$candidates): ?ChoiceSequence
            {
                $candidates[] = implode(',', $candidate->choices());

                return null;
            },
            1000,
        );

        $shrinker->shrink(new ChoiceSequence([3, 9, 27, 81], [new Span(0, 2, 'element', null), new Span(2, 4, 'element', null)]));

        $this->assertSame($candidates, array_values(array_unique($candidates)));
    }

    public function testReplacesASpanWithAShorterSpanThatItContainsAndThatHasTheSameLabel(): void
    {
        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate): ?ChoiceSequence
            {
                if ($candidate->choices() !== [3]) {
                    return null;
                }

                return new ChoiceSequence([3], [new Span(0, 1, 'tree', null)]);
            },
            1000,
        );

        $result = $shrinker->shrink(
            new ChoiceSequence(
                [1, 2, 3, 4, 5],
                [new Span(0, 5, 'tree', null), new Span(1, 4, 'list', 0), new Span(2, 3, 'tree', 1)],
            ),
        );

        $this->assertSame([3], $result->sequence()->choices());
        $this->assertSame(1, $result->numberOfSteps());
    }

    public function testReplacesSpansOfTheSameLengthWithShorterSpansTheyContainAndThatHaveTheSameLabel(): void
    {
        $falsifying = [
            '2,4,5,6' => [new Span(0, 1, 'tree', null), new Span(1, 4, 'tree', null), new Span(2, 3, 'tree', 1)],
            '1,2,3,5' => [new Span(0, 3, 'tree', null), new Span(1, 2, 'tree', 0), new Span(3, 4, 'tree', null)],
            '2,5'     => [new Span(0, 1, 'tree', null), new Span(1, 2, 'tree', null)],
        ];

        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate) use ($falsifying): ?ChoiceSequence
            {
                $key = implode(',', $candidate->choices());

                if (!isset($falsifying[$key])) {
                    return null;
                }

                return new ChoiceSequence($candidate->choices(), $falsifying[$key]);
            },
            1000,
        );

        $result = $shrinker->shrink(
            new ChoiceSequence(
                [1, 2, 3, 4, 5, 6],
                [new Span(0, 3, 'tree', null), new Span(1, 2, 'tree', 0), new Span(3, 6, 'tree', null), new Span(4, 5, 'tree', 2)],
            ),
        );

        $this->assertSame([2, 5], $result->sequence()->choices());
        $this->assertSame(2, $result->numberOfSteps());
    }

    public function testDoesNotReplaceASpanWithASpanThatItContainsAndThatHasAnotherLabel(): void
    {
        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate): ?ChoiceSequence
            {
                if ($candidate->choices() !== [3]) {
                    return null;
                }

                return new ChoiceSequence([3], [new Span(0, 1, 'leaf', null)]);
            },
            1000,
        );

        $result = $shrinker->shrink(
            new ChoiceSequence(
                [1, 2, 3, 4, 5],
                [new Span(0, 5, 'tree', null), new Span(2, 3, 'leaf', 0)],
            ),
        );

        $this->assertSame([1, 2, 3, 4, 5], $result->sequence()->choices());
    }

    public function testLowersAChoiceWhileRaisingALaterChoiceToTheLargestValue(): void
    {
        $shrinker = $this->shrinker(
            Generators::tuples(Generators::integers(0, 100), Generators::integers(0, 100)),
            static fn (array $value): bool => $value[0] * $value[1] >= 1000,
        );

        $this->assertSame([10, 100], $shrinker->shrink(new ChoiceSequence([20, 50]))->sequence()->choices());
    }

    public function testStartsEachRoundOfPassesWithThePassesThatMadeTheCounterexampleSimplerMostOftenInTheRoundBefore(): void
    {
        $candidates = [];

        // only lowering the first choice while raising the second one to its
        // largest value makes the counterexample simpler in the first round
        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate) use (&$candidates): ?ChoiceSequence
            {
                $candidates[] = $candidate->choices();

                if ($candidate->choices() !== [4, PHP_INT_MAX]) {
                    return null;
                }

                return $candidate;
            },
            1000,
        );

        $shrinker->shrink(new ChoiceSequence([5, 5]));

        $index = array_search([4, PHP_INT_MAX], $candidates, true);

        $this->assertIsInt($index);
        $this->assertArrayHasKey($index + 1, $candidates);
        $this->assertSame([3, PHP_INT_MAX], $candidates[$index + 1]);
    }

    public function testSortsSpansThatArePartOfTheSameSpanAndHaveTheSameLabel(): void
    {
        $elements = [new Span(0, 2, 'element', null), new Span(2, 4, 'element', null)];

        $result = $this->shrinkerAccepting(['5,6,1,2' => $elements, '1,2,5,6' => $elements])->shrink(new ChoiceSequence([5, 6, 1, 2], $elements));

        $this->assertSame([1, 2, 5, 6], $result->sequence()->choices());
    }

    public function testDoesNotSortSpansThatHaveDifferentLabels(): void
    {
        $elements = [new Span(0, 2, 'element', null), new Span(2, 4, 'other element', null)];

        $result = $this->shrinkerAccepting(['5,6,1,2' => $elements, '1,2,5,6' => $elements])->shrink(new ChoiceSequence([5, 6, 1, 2], $elements));

        $this->assertSame([5, 6, 1, 2], $result->sequence()->choices());
    }

    public function testDoesNotSortSpansThatArePartOfDifferentSpans(): void
    {
        $elements = [new Span(0, 2, 'pair', null), new Span(0, 2, 'element', 0), new Span(2, 4, 'other pair', null), new Span(2, 4, 'element', 2)];

        $result = $this->shrinkerAccepting(['5,6,1,2' => $elements, '1,2,5,6' => $elements])->shrink(new ChoiceSequence([5, 6, 1, 2], $elements));

        $this->assertSame([5, 6, 1, 2], $result->sequence()->choices());
    }

    public function testSwapsAdjacentSpansThatAreOutOfOrderWhenSortingAllOfThemDoesNotKeepThePropertyFalsified(): void
    {
        $elements = [new Span(0, 2, 'element', null), new Span(2, 4, 'element', null), new Span(4, 6, 'element', null)];

        $result = $this->shrinkerAccepting(['5,6,3,4,1,2' => $elements, '3,4,5,6,1,2' => $elements])->shrink(new ChoiceSequence([5, 6, 3, 4, 1, 2], $elements));

        $this->assertSame([3, 4, 5, 6, 1, 2], $result->sequence()->choices());
    }

    public function testKeepsTheChoicesBetweenSpansThatAreSortedWhereTheyAre(): void
    {
        $elements = [new Span(0, 2, 'element', null), new Span(3, 5, 'element', null)];

        $result = $this->shrinkerAccepting(['5,6,9,1,2,9' => $elements, '1,2,9,5,6,9' => $elements])->shrink(new ChoiceSequence([5, 6, 9, 1, 2, 9], $elements));

        $this->assertSame([1, 2, 9, 5, 6, 9], $result->sequence()->choices());
    }

    public function testKeepsTheChoiceSequenceThatTheReplayOfACandidateConsumed(): void
    {
        $shrinker = new Shrinker(
            static fn (ChoiceSequence $candidate): ChoiceSequence => new ChoiceSequence([0]),
            1000,
        );

        $this->assertSame([0], $shrinker->shrink(new ChoiceSequence([5, 5, 5]))->sequence()->choices());
    }

    public function testDoesNotKeepAConsumedChoiceSequenceThatIsNotSimplerThanTheCurrentOne(): void
    {
        $shrinker = new Shrinker(
            static fn (ChoiceSequence $candidate): ChoiceSequence => new ChoiceSequence([5, 5, 5]),
            1000,
        );

        $result = $shrinker->shrink(new ChoiceSequence([5, 5, 5]));

        $this->assertSame([5, 5, 5], $result->sequence()->choices());
        $this->assertSame(0, $result->numberOfSteps());
    }

    public function testStopsMinimizingAChoiceThatTheConsumedChoiceSequenceNoLongerContains(): void
    {
        // the binary search on the only choice first tries 51, the replay of
        // which consumes no choice at all
        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate): ?ChoiceSequence
            {
                if ($candidate->choices() === [51]) {
                    return new ChoiceSequence([]);
                }

                return null;
            },
            1000,
        );

        $this->assertSame([], $shrinker->shrink(new ChoiceSequence([100]))->sequence()->choices());
    }

    public function testStopsLoweringEqualChoicesThatTheConsumedChoiceSequenceDoesNotHave(): void
    {
        // lowering both choices together to a small value does not falsify
        // the property, lowering them to 51 does, but the replay of that
        // candidate consumes a different choice sequence
        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate): ?ChoiceSequence
            {
                if ($candidate->choices() === [51, 51]) {
                    return new ChoiceSequence([50, 51]);
                }

                return null;
            },
            1000,
        );

        $this->assertSame(
            [50, 51],
            $shrinker->shrink(new ChoiceSequence([100, 100], [new Span(0, 1, 'element', null), new Span(1, 2, 'element', null)]))->sequence()->choices(),
        );
    }

    public function testDoesNotLowerChoicesThatAreNoLongerEqualTogether(): void
    {
        // lowering the first choices of both spans together falsifies the
        // property, but the replay of that candidate consumes a choice
        // sequence in which the second choices of the spans differ
        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate): ?ChoiceSequence
            {
                if ($candidate->choices() === [0, 7, 0, 7]) {
                    return new ChoiceSequence([0, 7, 0, 8]);
                }

                return null;
            },
            1000,
        );

        $this->assertSame(
            [0, 7, 0, 8],
            $shrinker->shrink(new ChoiceSequence([5, 7, 5, 7], [new Span(0, 2, 'element', null), new Span(2, 4, 'element', null)]))->sequence()->choices(),
        );
    }

    public function testStopsRedistributingAPairOfChoicesThatTheConsumedChoiceSequenceDoesNotHave(): void
    {
        // moving everything from the first choice to the second one does not
        // falsify the property, moving half of it does, but the replay of
        // that candidate consumes a different choice sequence
        $shrinker = new Shrinker(
            static function (ChoiceSequence $candidate): ?ChoiceSequence
            {
                $choices = $candidate->choices();

                if (count($choices) !== 2 || $choices[0] + $choices[1] !== 20 || $choices[1] > 15) {
                    return null;
                }

                if ($choices === [10, 10]) {
                    return new ChoiceSequence([10, 9]);
                }

                return $candidate;
            },
            1000,
        );

        $this->assertSame([10, 9], $shrinker->shrink(new ChoiceSequence([20, 0]))->sequence()->choices());
    }

    /**
     * Replays a candidate the way a property does: the candidate falsifies
     * the property when the value it is replayed as falsifies it, and a
     * candidate that is rejected does not.
     *
     * @param ValueGenerator<mixed> $generator
     * @param Closure(mixed): bool  $falsifies
     * @param non-negative-int      $maximumNumberOfAttempts
     */
    private function shrinker(ValueGenerator $generator, Closure $falsifies, int $maximumNumberOfAttempts = 1000): Shrinker
    {
        $generator = AbstractGenerator::from($generator);

        return new Shrinker(
            static function (ChoiceSequence $candidate) use ($generator, $falsifies): ?ChoiceSequence
            {
                $source = ChoiceSource::replay($candidate);

                try {
                    $value = $generator->generate($source);
                } catch (ExampleRejectedException) {
                    return null;
                }

                if (!$falsifies($value)) {
                    return null;
                }

                return $source->sequence();
            },
            $maximumNumberOfAttempts,
        );
    }

    /**
     * @param ValueGenerator<mixed> $generator
     * @param Closure(mixed): bool  $falsifies
     */
    private function falsifyingSequence(ValueGenerator $generator, Closure $falsifies): ChoiceSequence
    {
        $generator  = AbstractGenerator::from($generator);
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));

        for ($attempt = 0; $attempt < 1000; $attempt++) {
            $source = ChoiceSource::random($randomizer);

            try {
                $value = $generator->generate($source);
            } catch (ExampleRejectedException) {
                continue;
            }

            if ($falsifies($value)) {
                return $source->sequence();
            }
        }

        $this->fail('No counterexample was generated');
    }

    /**
     * @param ValueGenerator<mixed> $generator
     */
    private function replay(ValueGenerator $generator, ChoiceSequence $sequence): mixed
    {
        return AbstractGenerator::from($generator)->generate(ChoiceSource::replay($sequence));
    }

    /**
     * @param array<string, list<Span>> $falsifying the spans of the choice sequences that falsify the property, by their choices separated by commas
     */
    private function shrinkerAccepting(array $falsifying): Shrinker
    {
        return new Shrinker(
            static function (ChoiceSequence $candidate) use ($falsifying): ?ChoiceSequence
            {
                $key = implode(',', $candidate->choices());

                if (!isset($falsifying[$key])) {
                    return null;
                }

                return new ChoiceSequence($candidate->choices(), $falsifying[$key]);
            },
            1000,
        );
    }

    /**
     * @return ValueGenerator<mixed>
     */
    private static function trees(): ValueGenerator
    {
        return Generators::deferred(
            static fn (): ValueGenerator => Generators::oneOf(Generators::integers(0, 100), Generators::tuples(self::trees(), self::trees())),
        );
    }
}
