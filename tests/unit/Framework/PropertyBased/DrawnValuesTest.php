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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\StateMachine\CounterMachine;
use PropertyBasedNameResolutionClassInGlobalNamespace;
use ReflectionMethod;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;
use stdClass;

#[CoversClass(DrawnValues::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class DrawnValuesTest extends TestCase
{
    public function testDrawsValuesFromTheChoiceSource(): void
    {
        $source      = ChoiceSource::replay(new ChoiceSequence([3, 5]));
        $drawnValues = DrawnValues::from($source);

        $this->assertSame(3, $drawnValues->draw(Generators::integers(0, 9), 'first'));
        $this->assertSame(5, $drawnValues->draw(Generators::integers(0, 9), null));
        $this->assertSame([3, 5], $source->sequence()->choices());
    }

    public function testDoesNotExportValuesThatAreNotReported(): void
    {
        $drawnValues = DrawnValues::from(ChoiceSource::replay(new ChoiceSequence([3])));

        $drawnValues->draw(Generators::integers(0, 9), 'value');

        $this->assertSame([], $drawnValues->exported());
    }

    public function testExportsValuesThatAreReportedWithTheirLabelsInTheOrderInWhichTheyWereDrawn(): void
    {
        $drawnValues = DrawnValues::reportedFrom(ChoiceSource::replay(new ChoiceSequence([3, 5])), $this->exporter());

        $drawnValues->draw(Generators::integers(0, 9), 'first');
        $drawnValues->draw(Generators::integers(0, 9), null);

        $this->assertSame(
            [
                ['label' => 'first', 'value' => '3'],
                ['label' => null, 'value' => '5'],
            ],
            $drawnValues->exported(),
        );
    }

    public function testExportsAValueWhenItIsDrawnBeforeItsStateCanChange(): void
    {
        $drawnValues = DrawnValues::reportedFrom(ChoiceSource::replay(new ChoiceSequence([3])), $this->exporter());

        $object = $drawnValues->draw(Generators::integers(0, 9)->map(static fn (int $value): stdClass => (object) ['value' => $value]), null);

        $object->value = 7;

        $this->assertStringContainsString("'value' => 3", $drawnValues->exported()[0]['value']);
        $this->assertStringNotContainsString("'value' => 7", $drawnValues->exported()[0]['value']);
    }

    public function testExportsValuesThatAreNotReportedOnRequest(): void
    {
        $drawnValues = DrawnValues::from(ChoiceSource::replay(new ChoiceSequence([3, 5])));

        $drawnValues->draw(Generators::integers(0, 9), 'first');
        $drawnValues->draw(Generators::integers(0, 9), null);

        $this->assertSame(
            [
                ['label' => 'first', 'value' => '3'],
                ['label' => null, 'value' => '5'],
            ],
            $drawnValues->exportedWith($this->exporter()),
        );

        $this->assertSame([], $drawnValues->exported());
    }

    public function testDoesNotExportValuesThatAreReportedAgainOnRequest(): void
    {
        $drawnValues = DrawnValues::reportedFrom(ChoiceSource::replay(new ChoiceSequence([3])), $this->exporter());

        $drawnValues->draw(Generators::integers(0, 9), 'value');

        $this->assertSame([['label' => 'value', 'value' => '3']], $drawnValues->exportedWith($this->exporter()));
    }

    public function testHasNoStepsToExportWithoutAStateMachine(): void
    {
        $drawnValues = DrawnValues::from(ChoiceSource::replay(new ChoiceSequence([])));

        $this->assertSame([], $drawnValues->stepsExportedWith($this->exporter()));
    }

    public function testExportsTheStepsOfItsStateMachineThatAreNotReportedOnRequest(): void
    {
        $drawnValues = DrawnValues::from(ChoiceSource::replay(new ChoiceSequence([0, 1, 1, 0, 1, 0, 1, 3, 0, 0])));

        $drawnValues->runStateMachine(new CounterMachine, 50);

        $this->assertSame([], $drawnValues->steps());
        $this->assertSame(
            [
                '$machine->start(value: 0);',
                '$machine->increment(amount: 1);',
                '$snapshots1 = $machine->snapshot();',
                '$machine->restore(snapshot: $snapshots1);',
            ],
            $drawnValues->stepsExportedWith($this->exporter()),
        );
    }

    private function exporter(): CounterexampleExporter
    {
        return new CounterexampleExporter(
            NameScope::forMethod(new ReflectionMethod(PropertyBasedNameResolutionClassInGlobalNamespace::class, 'method')),
            new Constructions,
        );
    }
}
