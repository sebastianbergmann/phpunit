<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\DeprecationCollector;

use const E_USER_DEPRECATED;
use function hrtime;
use function trigger_error;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\CollectingDispatcher;
use PHPUnit\Event\DirectDispatcher;
use PHPUnit\Event\Telemetry;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\DeprecationTriggeredSubscriber;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use PHPUnit\Event\TypeMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(Facade::class)]
#[Small]
#[Group('test-runner')]
final class FacadeTest extends TestCase
{
    public function testCollectorIsCreatedOnlyOnce(): void
    {
        $this->assertSame(Facade::collector(), Facade::collector());
    }

    public function testInitCreatesCollector(): void
    {
        Facade::init();

        $this->assertSame(Facade::collector(), Facade::collector());
    }

    public function testInitForIsolationCreatesCollector(): void
    {
        $property    = new ReflectionProperty(Facade::class, 'inIsolation');
        $inIsolation = $property->getValue();

        try {
            Facade::initForIsolation($this->dispatcher());

            $this->assertTrue($property->getValue());
            $this->assertSame(Facade::collector(), Facade::collector());
        } finally {
            $property->setValue(null, $inIsolation);
        }
    }

    #[IgnoreDeprecations]
    public function testForgetsTheDeprecationsOfThePreviousTestWhenATestIsPreparedInAnIsolatedProcess(): void
    {
        $property    = new ReflectionProperty(Facade::class, 'inIsolation');
        $inIsolation = $property->getValue();

        try {
            $dispatcher = $this->dispatcher();

            Facade::initForIsolation($dispatcher);

            trigger_error('message', E_USER_DEPRECATED);

            $this->assertContains('message', Facade::deprecations());

            $dispatcher->dispatch(new Prepared($this->telemetryInfo(), TestMethodBuilder::fromTestCase($this)));

            $this->assertSame([], Facade::deprecations());
        } finally {
            $property->setValue(null, $inIsolation);
        }
    }

    public function testDoesNotReportFilteredDeprecationsInAnIsolatedProcess(): void
    {
        $inIsolationProperty          = new ReflectionProperty(Facade::class, 'inIsolation');
        $filteredDeprecationsProperty = new ReflectionProperty(Collector::class, 'filteredDeprecations');
        $collector                    = Facade::collector();
        $inIsolation                  = $inIsolationProperty->getValue();
        $filteredDeprecations         = $filteredDeprecationsProperty->getValue($collector);

        try {
            $filteredDeprecationsProperty->setValue($collector, ['deprecation']);
            $inIsolationProperty->setValue(null, true);

            $this->assertSame([], Facade::filteredDeprecations());
        } finally {
            $inIsolationProperty->setValue(null, $inIsolation);
            $filteredDeprecationsProperty->setValue($collector, $filteredDeprecations);
        }
    }

    public function testDeprecationsAreCollected(): void
    {
        $this->assertSame(Facade::collector()->deprecations(), Facade::deprecations());
    }

    public function testFilteredDeprecationsAreCollected(): void
    {
        $this->assertSame(Facade::collector()->filteredDeprecations(), Facade::filteredDeprecations());
    }

    private function dispatcher(): CollectingDispatcher
    {
        $typeMap = new TypeMap;

        $typeMap->addMapping(DeprecationTriggeredSubscriber::class, DeprecationTriggered::class);
        $typeMap->addMapping(PreparedSubscriber::class, Prepared::class);

        return new CollectingDispatcher(new DirectDispatcher($typeMap));
    }

    private function telemetryInfo(): Telemetry\Info
    {
        return new Telemetry\Info(
            new Telemetry\Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                Telemetry\MemoryUsage::fromBytes(1000),
                Telemetry\MemoryUsage::fromBytes(2000),
                new Telemetry\GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            ),
            Telemetry\Duration::fromSecondsAndNanoseconds(123, 456),
            Telemetry\MemoryUsage::fromBytes(2000),
            Telemetry\Duration::fromSecondsAndNanoseconds(234, 567),
            Telemetry\MemoryUsage::fromBytes(3000),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            12345,
        );
    }
}
