<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use function realpath;
use PHPUnit\Event\Event;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use ReflectionProperty;

#[CoversClass(TestCase::class)]
#[Medium]
#[Group('test-runner')]
#[Group('test-runner/phpt')]
final class TestCaseTest extends FrameworkTestCase
{
    public function testSortIdReturnsFilename(): void
    {
        $filename = realpath(__DIR__ . '/../../../_files/success.phpt');
        $testCase = new TestCase($filename);

        $this->assertSame($filename, $testCase->sortId());
    }

    public function testCountReturnsOne(): void
    {
        $filename = realpath(__DIR__ . '/../../../_files/success.phpt');
        $testCase = new TestCase($filename);

        $this->assertSame(1, $testCase->count());
    }

    public function testCanBeConstructedForFileThatDoesNotExist(): void
    {
        $testCase = new TestCase('/this/file/does/not/exist.phpt');

        $this->assertSame(1, $testCase->count());
    }

    #[TestDox('Runs the code of the file referenced by the FILE_EXTERNAL section')]
    public function testRunsCodeOfExternalFile(): void
    {
        $events = $this->runAndTraceEvents(__DIR__ . '/../../../_files/phpt/file-external/test.phpt');

        $this->assertContainsOnlyInstancesOf(Event::class, $events);
        $this->assertNotEmpty($this->eventsOfType($events, Passed::class));
    }

    #[TestDox('Uses a default message when the SKIPIF section does not provide one')]
    public function testUsesDefaultMessageWhenSkipIfSectionDoesNotProvideOne(): void
    {
        $events = $this->runAndTraceEvents(__DIR__ . '/../../../_files/phpt/skipif-no-message.phpt');

        $skipped = $this->eventsOfType($events, Skipped::class);

        $this->assertCount(1, $skipped);
        $this->assertSame('Skipped', $skipped[0]->message());
    }

    /**
     * @param list<Event>         $events
     * @param class-string<Event> $type
     *
     * @return list<Event>
     */
    private function eventsOfType(array $events, string $type): array
    {
        $result = [];

        foreach ($events as $event) {
            if ($event instanceof $type) {
                $result[] = $event;
            }
        }

        return $result;
    }

    /**
     * @return list<Event>
     */
    private function runAndTraceEvents(string $filename): array
    {
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

        /*
         * The events that running the PHPT test emits must not end up in the
         * result of the test run that exercises the PHPT test runner, so they
         * are emitted into a throw-away event facade.
         */
        $property = new ReflectionProperty(Facade::class, 'instance');
        $instance = $property->getValue();

        $property->setValue(null, $facade);

        try {
            new TestCase(realpath($filename))->run();
        } finally {
            $property->setValue(null, $instance);
        }

        return $tracer->events;
    }
}
