<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use function date_default_timezone_get;
use DateTimeZone;
use PHPUnit\Event\Telemetry\System;
use PHPUnit\Event\Telemetry\SystemClock;
use PHPUnit\Event\Telemetry\SystemMemoryMeter;

final class Facade
{
    private ?TypeMap $typeMap = null;

    private ?Emitter $emitter = null;

    private ?Dispatcher $dispatcher = null;

    /**
     * @param class-string $subscriberInterface
     * @param class-string $eventClass
     */
    public function registerTypeMapping(string $subscriberInterface, string $eventClass): void
    {
        $this->typeMap()->addMapping($subscriberInterface, $eventClass);
    }

    public function registerSubscriber(Subscriber $subscriber): void
    {
        $this->dispatcher()->register($subscriber);
    }

    public function emitter(): Emitter
    {
        if ($this->emitter === null) {
            $this->emitter = new Emitter(
                $this->dispatcher(),
                new System(
                    new SystemClock(new DateTimeZone(date_default_timezone_get())),
                    new SystemMemoryMeter()
                )
            );
        }

        return $this->emitter;
    }

    private function dispatcher(): Dispatcher
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = new Dispatcher(
                $this->typeMap()
            );
            $this->registerDefaultSubscribers();
        }

        return $this->dispatcher;
    }

    private function typeMap(): TypeMap
    {
        if ($this->typeMap === null) {
            $this->typeMap = new TypeMap();
            $this->registerDefaultTypes();
        }

        return $this->typeMap;
    }

    private function registerDefaultTypes(): void
    {
        $defaultEvents = [
            Application\Configured::class,
            Application\Started::class,
            Assertion\Made::class,
            Bootstrap\Finished::class,
            Comparator\Registered::class,
            Extension\Loaded::class,
            GlobalState\Captured::class,
            GlobalState\Modified::class,
            GlobalState\Restored::class,
            Test\RunConfigured::class,
            Test\RunErrored::class,
            Test\RunFailed::class,
            Test\RunFinished::class,
            Test\RunPassed::class,
            Test\RunRisky::class,
            Test\RunSkippedByDataProvider::class,
            Test\RunSkippedIncomplete::class,
            Test\RunSkippedWithFailedRequirements::class,
            Test\RunSkippedWithWarning::class,
            Test\RunStarted::class,
            Test\SetUpFinished::class,
            Test\TearDownFinished::class,
            TestCase\AfterClassFinished::class,
            TestCase\BeforeClassFinished::class,
            TestCase\SetUpBeforeClassFinished::class,
            TestCase\SetUpFinished::class,
            TestCase\TearDownAfterClassFinished::class,
            TestDouble\MockCreated::class,
            TestDouble\MockForTraitCreated::class,
            TestDouble\PartialMockCreated::class,
            TestDouble\ProphecyCreated::class,
            TestDouble\TestProxyCreated::class,
            TestSuite\AfterClassFinished::class,
            TestSuite\BeforeClassFinished::class,
            TestSuite\Configured::class,
            TestSuite\Loaded::class,
            TestSuite\RunFinished::class,
            TestSuite\RunStarted::class,
            TestSuite\Sorted::class,
        ];

        foreach ($defaultEvents as $eventClass) {
            $this->registerTypeMapping(
                $eventClass . 'Subscriber',
                $eventClass
            );
        }
    }

    private function registerDefaultSubscribers(): void
    {
    }
}
