<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function array_key_last;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use PHPUnit\Event\Event;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\TestRunner\WarningTriggered;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\Util\PHP\JobRunner;
use PHPUnit\Util\PHP\JobRunnerRegistry;
use ReflectionProperty;
use ValueError;

#[CoversClass(PhptRepeatTestSuite::class)]
#[CoversClass(PhptIterativeTestSuite::class)]
#[Medium]
final class PhptRepeatTestSuiteTest extends TestCase
{
    public function testForwardsTheEventsOfARunWhoseChildProcessCannotBeStartedAndStopsCollectingEventsBeforeTheExceptionPropagates(): void
    {
        // The child process of the FILE section cannot be started: a NUL byte
        // in a setting of the INI section makes proc_open() reject it.
        $file = tempnam(sys_get_temp_dir(), 'phpunit_');

        $this->assertNotFalse($file);

        file_put_contents($file, "--TEST--\nA PHPT test whose child process cannot be started\n--INI--\ndisplay_errors=1\0x\n--FILE--\n<?php print 'ok';\n--EXPECT--\nok\n");

        $tracer = new class implements Tracer
        {
            /**
             * @var list<Event>
             */
            public array $events = [];

            public function trace(Event $event): void
            {
                $this->events[] = $event;
            }
        };

        $facade = new Facade;
        $facade->registerTracer($tracer);
        $facade->seal();

        // The events that running the PHPT test emits must not end up in the
        // result of the test run that exercises it, so they are emitted into
        // a throw-away event facade.
        $property = new ReflectionProperty(Facade::class, 'instance');
        $instance = $property->getValue();

        $property->setValue(null, $facade);

        // The PHPT test runs its child processes through the job runner of the
        // JobRunnerRegistry, which reports to the event facade that it was
        // created with. Were that job runner created while the throw-away
        // event facade is in place, it would report to it for the rest of the
        // test run, and the events of every test that runs in a separate
        // process afterwards would be lost.
        JobRunnerRegistry::set($this->jobRunnerForTheEventFacadeInPlace());

        $exception = null;

        try {
            PhptRepeatTestSuite::for($file, Facade::emitter(), 2)->run();
        } catch (ValueError $e) {
            $exception = $e;
        } finally {
            Facade::emitter()->testRunnerTriggeredPhpunitWarning('emitted after the exception');

            $property->setValue(null, $instance);

            JobRunnerRegistry::set($this->jobRunnerForTheEventFacadeInPlace());

            unlink($file);
        }

        $this->assertInstanceOf(ValueError::class, $exception);

        // The events that the first repetition emitted before its child
        // process was to be started were forwarded, and the facade no longer
        // collects events: the event emitted afterwards was dispatched, too.
        $this->assertSame(1, $this->numberOf($tracer->events, PreparationStarted::class));
        $this->assertSame(1, $this->numberOf($tracer->events, Prepared::class));
        $this->assertInstanceOf(WarningTriggered::class, $tracer->events[array_key_last($tracer->events)]);
    }

    private function jobRunnerForTheEventFacadeInPlace(): JobRunner
    {
        return new JobRunner(
            new ChildProcessResultProcessor(
                Facade::instance(),
                Facade::emitter(),
                PassedTests::instance(),
                CodeCoverage::instance(),
            ),
            Facade::emitter(),
        );
    }

    /**
     * @param list<Event>         $events
     * @param class-string<Event> $type
     */
    private function numberOf(array $events, string $type): int
    {
        $count = 0;

        foreach ($events as $event) {
            if ($event instanceof $type) {
                $count++;
            }
        }

        return $count;
    }
}
