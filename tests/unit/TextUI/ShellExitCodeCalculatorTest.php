<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use function hrtime;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\PhpunitNoticeTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\CliArguments\Builder as CliBuilder;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;

#[CoversClass(ShellExitCodeCalculator::class)]
#[Medium]
#[Group('textui')]
final class ShellExitCodeCalculatorTest extends TestCase
{
    public function testFailsOnPhpunitNoticeWhenRequested(): void
    {
        $this->assertSame(
            1,
            (new ShellExitCodeCalculator)->calculate(
                $this->configuration(['--fail-on-phpunit-notice']),
                $this->testResultWithPhpunitNotice(),
            ),
        );
    }

    public function testDoesNotFailOnPhpunitNoticeWhenRequested(): void
    {
        $this->assertSame(
            0,
            (new ShellExitCodeCalculator)->calculate(
                $this->configuration([
                    '--fail-on-all-issues',
                    '--do-not-fail-on-phpunit-notice',
                ]),
                $this->testResultWithPhpunitNotice(),
            ),
        );
    }

    /**
     * @param list<non-empty-string> $parameters
     */
    private function configuration(array $parameters): Configuration
    {
        return (new Merger)->merge(
            (new CliBuilder)->fromParameters($parameters),
            DefaultConfiguration::create(),
        );
    }

    private function testResultWithPhpunitNotice(): TestResult
    {
        $test = new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );

        $event = new PhpunitNoticeTriggered(
            $this->telemetryInfo(),
            $test,
            'message',
        );

        return new TestResult(
            1,
            1,
            1,
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            ['FooTest::testBar' => [$event]],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            0,
            [
                'self'     => 0,
                'direct'   => 0,
                'indirect' => 0,
                'unknown'  => 0,
            ],
        );
    }

    private function telemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000),
                new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
        );
    }
}
