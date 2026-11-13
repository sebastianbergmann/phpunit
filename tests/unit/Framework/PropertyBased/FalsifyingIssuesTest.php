<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function end;
use function explode;
use function hrtime;
use function in_array;
use PHPUnit\Event\Code\IssueTrigger\Code;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TextUI\CliArguments\Builder as CliBuilder;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;

#[CoversClass(FalsifyingIssues::class)]
#[Medium]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class FalsifyingIssuesTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{list<non-empty-string>, non-empty-string, ?non-empty-string}>
     */
    public static function issueProvider(): array
    {
        $kinds = [
            'E_USER_ERROR',
            'E_WARNING',
            'E_USER_WARNING',
            'E_NOTICE',
            'E_USER_NOTICE',
            'E_DEPRECATED',
            'E_USER_DEPRECATED',
            'self E_USER_DEPRECATED',
            'direct E_USER_DEPRECATED',
            'indirect E_DEPRECATED',
        ];

        $falsifying = [
            'no options'                                                  => ['E_USER_ERROR'],
            '--fail-on-warning'                                           => ['E_USER_ERROR', 'E_WARNING', 'E_USER_WARNING'],
            '--fail-on-notice'                                            => ['E_USER_ERROR', 'E_NOTICE', 'E_USER_NOTICE'],
            '--fail-on-deprecation'                                       => ['E_USER_ERROR', 'E_DEPRECATED', 'E_USER_DEPRECATED', 'self E_USER_DEPRECATED', 'direct E_USER_DEPRECATED', 'indirect E_DEPRECATED'],
            '--fail-on-self-deprecation'                                  => ['E_USER_ERROR', 'self E_USER_DEPRECATED'],
            '--fail-on-direct-deprecation'                                => ['E_USER_ERROR', 'direct E_USER_DEPRECATED'],
            '--fail-on-indirect-deprecation'                              => ['E_USER_ERROR', 'indirect E_DEPRECATED'],
            '--fail-on-all-issues'                                        => $kinds,
            '--fail-on-all-issues --do-not-fail-on-warning'               => ['E_USER_ERROR', 'E_NOTICE', 'E_USER_NOTICE', 'E_DEPRECATED', 'E_USER_DEPRECATED', 'self E_USER_DEPRECATED', 'direct E_USER_DEPRECATED', 'indirect E_DEPRECATED'],
            '--fail-on-all-issues --do-not-fail-on-notice'                => ['E_USER_ERROR', 'E_WARNING', 'E_USER_WARNING', 'E_DEPRECATED', 'E_USER_DEPRECATED', 'self E_USER_DEPRECATED', 'direct E_USER_DEPRECATED', 'indirect E_DEPRECATED'],
            '--fail-on-all-issues --do-not-fail-on-deprecation'           => ['E_USER_ERROR', 'E_WARNING', 'E_USER_WARNING', 'E_NOTICE', 'E_USER_NOTICE'],
            '--fail-on-deprecation --do-not-fail-on-self-deprecation'     => ['E_USER_ERROR', 'E_DEPRECATED', 'E_USER_DEPRECATED', 'direct E_USER_DEPRECATED', 'indirect E_DEPRECATED'],
            '--fail-on-deprecation --do-not-fail-on-direct-deprecation'   => ['E_USER_ERROR', 'E_DEPRECATED', 'E_USER_DEPRECATED', 'self E_USER_DEPRECATED', 'indirect E_DEPRECATED'],
            '--fail-on-deprecation --do-not-fail-on-indirect-deprecation' => ['E_USER_ERROR', 'E_DEPRECATED', 'E_USER_DEPRECATED', 'self E_USER_DEPRECATED', 'direct E_USER_DEPRECATED'],
        ];

        $data = [];

        foreach ($falsifying as $options => $falsifyingKinds) {
            $parameters = [];

            if ($options !== 'no options') {
                $parameters = explode(' ', $options);
            }

            foreach ($kinds as $kind) {
                $description = null;

                if (in_array($kind, $falsifyingKinds, true)) {
                    $words       = explode(' ', $kind);
                    $description = end($words) . ' at file.php:1';
                }

                $data[$kind . ' with ' . $options] = [$parameters, $kind, $description];
            }
        }

        return $data;
    }

    /**
     * @return array<non-empty-string, array{non-empty-string}>
     */
    public static function issueThatIsNotReportedProvider(): array
    {
        return [
            'suppressed E_USER_ERROR'       => ['suppressed E_USER_ERROR'],
            'suppressed E_WARNING'          => ['suppressed E_WARNING'],
            'suppressed E_USER_WARNING'     => ['suppressed E_USER_WARNING'],
            'suppressed E_NOTICE'           => ['suppressed E_NOTICE'],
            'suppressed E_USER_NOTICE'      => ['suppressed E_USER_NOTICE'],
            'suppressed E_DEPRECATED'       => ['suppressed E_DEPRECATED'],
            'suppressed E_USER_DEPRECATED'  => ['suppressed E_USER_DEPRECATED'],
            'E_WARNING in baseline'         => ['baselined E_WARNING'],
            'E_USER_WARNING in baseline'    => ['baselined E_USER_WARNING'],
            'E_NOTICE in baseline'          => ['baselined E_NOTICE'],
            'E_USER_NOTICE in baseline'     => ['baselined E_USER_NOTICE'],
            'E_DEPRECATED in baseline'      => ['baselined E_DEPRECATED'],
            'E_USER_DEPRECATED in baseline' => ['baselined E_USER_DEPRECATED'],
        ];
    }

    /**
     * @param list<non-empty-string> $parameters
     * @param non-empty-string       $kind
     * @param ?non-empty-string      $description
     */
    #[DataProvider('issueProvider')]
    public function testAnIssueFalsifiesThePropertyWhenTheTestRunFailsBecauseOfIt(array $parameters, string $kind, ?string $description): void
    {
        $falsification = $this->falsifyingIssues($parameters)->falsificationBy($this->events($kind));

        if ($description === null) {
            $this->assertNull($falsification);

            return;
        }

        $this->assertNotNull($falsification);
        $this->assertSame($description, $falsification->description());
        $this->assertTrue($falsification->isIssue());
        $this->assertStringEndsWith(' was triggered at file.php:1: message', $falsification->message());
    }

    /**
     * @param non-empty-string $kind
     */
    #[DataProvider('issueThatIsNotReportedProvider')]
    public function testAnIssueThatIsNotReportedDoesNotFalsifyTheProperty(string $kind): void
    {
        $this->assertNull($this->falsifyingIssues(['--fail-on-all-issues'])->falsificationBy($this->events($kind)));
    }

    public function testTheFirstIssueForWhichTheTestRunFailsFalsifiesTheProperty(): void
    {
        $events = new EventCollection;

        $events->add($this->event('E_USER_NOTICE'));
        $events->add($this->event('E_USER_WARNING'));
        $events->add($this->event('E_WARNING'));

        $falsification = $this->falsifyingIssues(['--fail-on-warning'])->falsificationBy($events);

        $this->assertNotNull($falsification);
        $this->assertSame('E_USER_WARNING at file.php:1', $falsification->description());
    }

    public function testAnExampleThatTriggeredNoIssueDoesNotFalsifyTheProperty(): void
    {
        $events = new EventCollection;

        $events->add(new Passed($this->telemetryInfo(), $this->testMethod()));

        $this->assertNull($this->falsifyingIssues(['--fail-on-all-issues'])->falsificationBy($events));
    }

    /**
     * @param list<non-empty-string> $parameters
     */
    private function falsifyingIssues(array $parameters): FalsifyingIssues
    {
        return FalsifyingIssues::fromConfiguration(
            new Merger($this->createStub(Emitter::class))->merge(
                new CliBuilder($this->createStub(Emitter::class))->fromParameters($parameters),
                DefaultConfiguration::create(),
            ),
        );
    }

    /**
     * @param non-empty-string $kind
     */
    private function events(string $kind): EventCollection
    {
        $events = new EventCollection;

        $events->add($this->event($kind));

        return $events;
    }

    /**
     * @param non-empty-string $kind
     */
    private function event(string $kind): Event
    {
        $words      = explode(' ', $kind);
        $suppressed = $words[0] === 'suppressed';
        $baselined  = $words[0] === 'baselined';
        $trigger    = IssueTrigger::from(null, null);

        if ($words[0] === 'self') {
            $trigger = IssueTrigger::from(Code::FirstParty, null);
        }

        if ($words[0] === 'direct') {
            $trigger = IssueTrigger::from(Code::ThirdParty, Code::FirstParty);
        }

        if ($words[0] === 'indirect') {
            $trigger = IssueTrigger::from(Code::ThirdParty, Code::ThirdParty);
        }

        return match (end($words)) {
            'E_USER_ERROR'      => new ErrorTriggered($this->telemetryInfo(), $this->testMethod(), 'message', 'file.php', 1, $suppressed),
            'E_WARNING'         => new PhpWarningTriggered($this->telemetryInfo(), $this->testMethod(), 'message', 'file.php', 1, $suppressed, $baselined),
            'E_USER_WARNING'    => new WarningTriggered($this->telemetryInfo(), $this->testMethod(), 'message', 'file.php', 1, $suppressed, $baselined),
            'E_NOTICE'          => new PhpNoticeTriggered($this->telemetryInfo(), $this->testMethod(), 'message', 'file.php', 1, $suppressed, $baselined),
            'E_USER_NOTICE'     => new NoticeTriggered($this->telemetryInfo(), $this->testMethod(), 'message', 'file.php', 1, $suppressed, $baselined),
            'E_DEPRECATED'      => new PhpDeprecationTriggered($this->telemetryInfo(), $this->testMethod(), 'message', 'file.php', 1, $suppressed, $baselined, false, false, $trigger),
            'E_USER_DEPRECATED' => new DeprecationTriggered($this->telemetryInfo(), $this->testMethod(), 'message', 'file.php', 1, $suppressed, $baselined, false, false, $trigger, 'stack trace'),
        };
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

    private function testMethod(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }
}
