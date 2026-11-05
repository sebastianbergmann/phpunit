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

use function array_map;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;
use SebastianBergmann\ChoiceSequence\ChoiceTree;
use SebastianBergmann\ChoiceSequence\Span;
use SebastianBergmann\ChoiceSequence\SpanKind;
use SebastianBergmann\ChoiceSequence\UniformDistribution;

#[CoversClass(ChoiceSource::class)]
#[CoversClass(ExampleRejectedException::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ChoiceSourceTest extends TestCase
{
    public function testRejectsTheExampleWhenItRequiresMoreThanTheMaximumNumberOfChoices(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]), 2);

        $source->draw(1, new UniformDistribution);
        $source->draw(1, new UniformDistribution);

        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessageIs('Generating the example required more than 2 choices');

        $source->draw(1, new UniformDistribution);
    }

    public function testRejectsTheExampleWhenChoosingAnElementRequiresMoreThanTheMaximumNumberOfChoices(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]), 1);

        $source->choose(['a', 'b'], new UniformDistribution);

        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessageIs('Generating the example required more than 1 choices');

        $source->choose(['a', 'b'], new UniformDistribution);
    }

    public function testDrawsAndReplaysChoicesWithTheChoiceSourceOfTheChoiceSequenceLibrary(): void
    {
        $tree   = new ChoiceTree;
        $source = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(1)), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, null, $tree);

        $source->startSpan('element');

        $choice  = $source->draw(10, new UniformDistribution);
        $element = $source->choose(['a', 'b', 'c'], new UniformDistribution);

        $source->endSpan();
        $source->exampleGenerated();

        $this->assertSame(2, $source->numberOfChoices());
        $this->assertFalse($source->drewDifferentlyThanBefore());
        $this->assertSame('candidate', $source->suggest(['candidate'], 1.0));
        $this->assertSame(['element'], array_map(static fn (Span $span): string => $span->label(), $source->sequence()->spans()));

        $replay = ChoiceSource::replay($source->sequence());

        $this->assertSame($choice, $replay->draw(10, new UniformDistribution));
        $this->assertSame($element, $replay->choose(['a', 'b', 'c'], new UniformDistribution));
        $this->assertSame($source->sequence()->choices(), $replay->sequence()->choices());
    }

    public function testMarksSpansWithTheirKinds(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([1, 2]));

        $source->startSpan('bytes', SpanKind::Bytes);
        $source->startSpan('byte', SpanKind::Element);
        $source->draw(1, new UniformDistribution);
        $source->draw(255, new UniformDistribution);
        $source->endSpan();
        $source->endSpan();

        $this->assertSame(
            [SpanKind::Bytes, SpanKind::Element],
            array_map(static fn (Span $span): ?SpanKind => $span->kind(), $source->sequence()->spans()),
        );
    }

    public function testProvidesTheDeriverItIsCreatedWith(): void
    {
        $deriver = new GeneratorDeriver(GeneratorRegistry::empty());

        $this->assertSame($deriver, ChoiceSource::replay(new ChoiceSequence([]), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver)->deriver());
    }

    public function testProvidesADeriverWithoutRegisteredGeneratorsWhenItIsCreatedWithoutOne(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $this->assertSame($source->deriver(), $source->deriver());
    }

    public function testRecordsHowObjectsWereConstructed(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));
        $object = new ChoiceSequence([]);

        $this->assertSame($source->constructions(), $source->constructions());
        $this->assertFalse($source->constructions()->has($object));

        $source->constructions()->record($object, ChoiceSequence::class, ['choices' => []]);

        $this->assertSame(['className' => ChoiceSequence::class, 'arguments' => ['choices' => []]], $source->constructions()->of($object));
    }

    public function testCountsTheObjectsOfAClassThatAreBeingGenerated(): void
    {
        $source = ChoiceSource::replay(new ChoiceSequence([]));

        $this->assertSame(1, $source->startObject(self::class));
        $this->assertSame(2, $source->startObject(self::class));
        $this->assertSame(1, $source->startObject(TestCase::class));

        $source->endObject(self::class);

        $this->assertSame(2, $source->startObject(self::class));

        $source->endObject(self::class);
        $source->endObject(self::class);

        $this->assertSame(1, $source->startObject(self::class));
    }
}
