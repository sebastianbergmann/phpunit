<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\PropertyFalsified;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Metadata\MetadataCollection;

#[CoversClass(PendingPropertyFalsified::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class PendingPropertyFalsifiedTest extends AbstractEventTestCase
{
    public function testReturnsTheEventThatWasRememberedForTheTest(): void
    {
        $pending = new PendingPropertyFalsified;
        $event   = $this->event();

        $pending->remember($event);

        $this->assertSame($event, $pending->takeFor($this->testValueObject()));
    }

    public function testReturnsNothingWhenNoEventWasRemembered(): void
    {
        $this->assertNull((new PendingPropertyFalsified)->takeFor($this->testValueObject()));
    }

    public function testReturnsNothingForAnotherTest(): void
    {
        $pending = new PendingPropertyFalsified;

        $pending->remember($this->event());

        $this->assertNull($pending->takeFor($this->otherTest()));
    }

    public function testForgetsTheEventOnceItWasTaken(): void
    {
        $pending = new PendingPropertyFalsified;

        $pending->remember($this->event());
        $pending->takeFor($this->otherTest());

        $this->assertNull($pending->takeFor($this->testValueObject()));
    }

    private function event(): PropertyFalsified
    {
        return new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 2, 3, 1, false, false, [0], ['value' => '0']);
    }

    private function otherTest(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBaz',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'baz'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }
}
