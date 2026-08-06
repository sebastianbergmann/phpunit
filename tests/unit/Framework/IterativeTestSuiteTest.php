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

use function realpath;
use PHPUnit\Event\EventsAreNotBeingCollectedException;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\TestFixture\ConcreteIterativeTestSuite;
use PHPUnit\TestFixture\TestThatThrowsWhenRun;
use ReflectionProperty;
use RuntimeException;

#[CoversClass(IterativeTestSuite::class)]
#[Small]
final class IterativeTestSuiteTest extends TestCase
{
    public function testDoesNotSetDependenciesOnTestThatIsNotTestCase(): void
    {
        $filename = realpath(__DIR__ . '/../../end-to-end/_files/phpt-expect-location-hint-example.phpt');

        $this->assertNotFalse($filename);

        $suite = ConcreteIterativeTestSuite::empty('the-name');
        $suite->addTest(new PhptTestCase($filename));

        $dependencies = [new ExecutionOrderDependency('PHPUnit\TestFixture\ExampleTest::testOne')];

        $suite->setDependencies($dependencies);

        $this->assertSame(
            $dependencies,
            new ReflectionProperty(IterativeTestSuite::class, 'dependencies')->getValue($suite),
        );
    }

    public function testStopsCollectingEventsWhenRunningTheTestThrows(): void
    {
        $suite = ConcreteIterativeTestSuite::empty('the-name');

        $message = null;

        try {
            $suite->runTestCollectingEvents(new TestThatThrowsWhenRun);
        } catch (RuntimeException $e) {
            $message = $e->getMessage();
        }

        $this->assertSame('the test could not be run', $message);

        // A dispatcher that is left collecting events swallows every event
        // that is emitted afterwards
        $this->expectException(EventsAreNotBeingCollectedException::class);

        EventFacade::instance()->stopCollectingEvents();
    }
}
