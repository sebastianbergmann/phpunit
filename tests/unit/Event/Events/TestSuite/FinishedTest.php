<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Finished::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/events')]
final class FinishedTest extends AbstractEventTestCase
{
    public function testConstructorSetsValues(): void
    {
        $telemetryInfo = $this->telemetryInfo();
        $testSuite     = $this->testSuiteValueObject();

        $event = new Finished(
            $telemetryInfo,
            $testSuite,
        );

        $this->assertSame($telemetryInfo, $event->telemetryInfo());
        $this->assertSame($testSuite, $event->testSuite());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $event = new Finished(
            $this->telemetryInfo(),
            $this->testSuiteValueObject(),
        );

        $this->assertSame('Test Suite Finished (foo, 9001 tests)', $event->asString());
    }

    public function testCanBeRepresentedAsStringForTestSuiteForTestMethodWithDataProvider(): void
    {
        $event = new Finished(
            $this->telemetryInfo(),
            new TestSuiteForTestMethodWithDataProvider(
                'PHPUnit\TestFixture\ExampleTest::testSomething',
                2,
                TestCollection::fromArray([]),
                'PHPUnit\TestFixture\ExampleTest',
                'testSomething',
                'ExampleTest.php',
                10,
            ),
        );

        $this->assertSame(
            'Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\ExampleTest::testSomething, 2 data sets)',
            $event->asString(),
        );
    }

    public function testCanBeRepresentedAsStringForTestSuiteForRepeatedTestMethod(): void
    {
        $event = new Finished(
            $this->telemetryInfo(),
            new TestSuiteForRepeatedTestMethod(
                'PHPUnit\TestFixture\ExampleTest::testSomething',
                3,
                TestCollection::fromArray([]),
                'PHPUnit\TestFixture\ExampleTest',
                'testSomething',
                'ExampleTest.php',
                10,
                false,
            ),
        );

        $this->assertSame(
            'Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\ExampleTest::testSomething, 3 repetitions)',
            $event->asString(),
        );
    }
}
