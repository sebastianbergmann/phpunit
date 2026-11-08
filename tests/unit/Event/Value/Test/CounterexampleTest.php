<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Counterexample::class)]
#[CoversClass(DrawnValue::class)]
#[CoversClass(OtherFalsification::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
final class CounterexampleTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $drawnValue     = new DrawnValue('index', '0');
        $counterexample = new Counterexample([1, 5, 0], ['size' => '1'], null, [$drawnValue], ['$machine->open();']);

        $this->assertSame([1, 5, 0], $counterexample->choiceSequence());
        $this->assertSame(['size' => '1'], $counterexample->arguments());
        $this->assertNull($counterexample->explicitExample());
        $this->assertSame([$drawnValue], $counterexample->drawnValues());
        $this->assertSame(['$machine->open();'], $counterexample->steps());
    }

    public function testCanBeAnExplicitExample(): void
    {
        $counterexample = new Counterexample([], ['value' => '5'], 'five', [], []);

        $this->assertSame([], $counterexample->choiceSequence());
        $this->assertSame('five', $counterexample->explicitExample());
    }

    public function testDrawnValueHasItsLabelAndValue(): void
    {
        $drawnValue = new DrawnValue('list', '[5]');

        $this->assertSame('list', $drawnValue->label());
        $this->assertSame('[5]', $drawnValue->value());
    }

    public function testDrawnValueCanHaveNoLabel(): void
    {
        $this->assertNull(new DrawnValue(null, '0')->label());
    }

    public function testOtherFalsificationHasItsDescriptionAndCounterexample(): void
    {
        $counterexample     = new Counterexample([3, 0], ['size' => '3'], null, [], []);
        $otherFalsification = new OtherFalsification('RuntimeException at tests/FooTest.php:12', $counterexample);

        $this->assertSame('RuntimeException at tests/FooTest.php:12', $otherFalsification->description());
        $this->assertSame($counterexample, $otherFalsification->counterexample());
    }
}
