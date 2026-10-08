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

use function array_sum;
use function array_unique;
use function array_values;
use function count;
use function implode;
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
        $this->assertFalse($original->isSimplerThan($result->sequence()));
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

        $shrinker->shrink(new ChoiceSequence([3, 9, 27, 81], [new Span(0, 2), new Span(2, 4)]));

        $this->assertSame($candidates, array_values(array_unique($candidates)));
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
}
