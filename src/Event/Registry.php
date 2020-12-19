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

final class Registry
{
    private static ?TypeMap $typeMap = null;

    private static ?Emitter $emitter = null;

    private static ?Dispatcher $dispatcher = null;

    public static function emitter(): Emitter
    {
        if (self::$emitter === null) {
            self::$emitter = new DispatchingEmitter(
                self::dispatcher(),
                new Telemetry\System(
                    new Telemetry\SystemStopWatch(),
                    new Telemetry\SystemMemoryMeter()
                )
            );
        }

        return self::$emitter;
    }

    private static function dispatcher(): Dispatcher
    {
        if (self::$dispatcher === null) {
            self::$dispatcher = new Dispatcher(self::typeMap());
        }

        return self::$dispatcher;
    }

    private static function typeMap(): TypeMap
    {
        if (self::$typeMap === null) {
            $typeMap = new TypeMap();

            self::registerDefaultTypes($typeMap);

            self::$typeMap = $typeMap;
        }

        return self::$typeMap;
    }

    private static function registerDefaultTypes(TypeMap $typeMap): void
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
            Test\AbortedWithMessage::class,
            Test\AfterLastTestMethodCalled::class,
            Test\AfterLastTestMethodFinished::class,
            Test\AfterTestMethodFinished::class,
            Test\BeforeFirstTestMethodCalled::class,
            Test\BeforeFirstTestMethodFinished::class,
            Test\BeforeTestMethodCalled::class,
            Test\BeforeTestMethodFinished::class,
            Test\Errored::class,
            Test\Failed::class,
            Test\Finished::class,
            Test\Passed::class,
            Test\PassedButRisky::class,
            Test\PostConditionCalled::class,
            Test\PreConditionCalled::class,
            Test\PreConditionFinished::class,
            Test\Prepared::class,
            Test\RunConfigured::class,
            Test\SetUpFinished::class,
            Test\SkippedByDataProvider::class,
            Test\SkippedDueToUnsatisfiedRequirements::class,
            Test\SkippedWithMessage::class,
            TestDouble\MockObjectCreated::class,
            TestDouble\MockObjectCreatedForAbstractClass::class,
            TestDouble\MockObjectCreatedForTrait::class,
            TestDouble\MockObjectCreatedFromWsdl::class,
            TestDouble\PartialMockObjectCreated::class,
            TestDouble\TestProxyCreated::class,
            TestDouble\TestStubCreated::class,
            TestSuite\AfterClassFinished::class,
            TestSuite\Loaded::class,
            TestSuite\RunFinished::class,
            TestSuite\Sorted::class,
            TestSuite\Started::class,
        ];

        foreach ($defaultEvents as $eventClass) {
            $typeMap->addMapping(
                $eventClass . 'Subscriber',
                $eventClass
            );
        }
    }
}
