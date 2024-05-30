<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace unit\Runner\Baseline;

use const true;
use function hrtime;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Runner\Baseline\Baseline;
use PHPUnit\Runner\Baseline\Generator;
use PHPUnit\Runner\Baseline\Issue;
use PHPUnit\TextUI\Configuration\FileCollection;

use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(Generator::class)]
#[Small]
final class GeneratorTest extends TestCase
{
    /**
     * @return iterable{
     *     source: Source,
     *     events: <DeprecationTriggered|NoticeTriggered|PhpDeprecationTriggered|PhpNoticeTriggered|PhpWarningTriggered|WarningTriggered>,
     *     expectedBaseline: Baseline
     * }
     */
    public static function provideIgnoreSuppressions(): iterable
    {
        $anyExistingFileName = __FILE__;

        $deprecation = self::buildDeprecationTriggeredEvent('deprecation message', $anyExistingFileName, 100, true);

        yield 'no suppression is ignored' => [
            'source' => self::buildSource(
                ignoreSuppressionOfDeprecations: false,
                ignoreSuppressionOfPhpDeprecations: false,
                ignoreSuppressionOfErrors: false,
                ignoreSuppressionOfNotices: false,
                ignoreSuppressionOfPhpNotices: false,
                ignoreSuppressionOfWarnings: false,
                ignoreSuppressionOfPhpWarnings: false,
            ),
            'events'           => [$deprecation],
            'expectedBaseline' => new Baseline,
        ];

        yield 'deprecation suppression is ignored and triggered deprecation is added to baseline' => [
            'source' => self::buildSource(
                ignoreSuppressionOfDeprecations: true,
                ignoreSuppressionOfPhpDeprecations: false,
                ignoreSuppressionOfErrors: false,
                ignoreSuppressionOfNotices: false,
                ignoreSuppressionOfPhpNotices: false,
                ignoreSuppressionOfWarnings: false,
                ignoreSuppressionOfPhpWarnings: false,
            ),
            'events'           => [$deprecation],
            'expectedBaseline' => self::buildSingleIssueBaseline($anyExistingFileName, 100, 'deprecation message'),
        ];

        $warning = self::buildWarningTriggeredEvent('warning message', $anyExistingFileName, 1, true);

        yield 'warning suppression is ignored and triggered warning is added to baseline' => [
            'source' => self::buildSource(
                ignoreSuppressionOfDeprecations: false,
                ignoreSuppressionOfPhpDeprecations: false,
                ignoreSuppressionOfErrors: false,
                ignoreSuppressionOfNotices: false,
                ignoreSuppressionOfPhpNotices: false,
                ignoreSuppressionOfWarnings: true,
                ignoreSuppressionOfPhpWarnings: false,
            ),
            'events'           => [$warning],
            'expectedBaseline' => self::buildSingleIssueBaseline($anyExistingFileName, 1, 'warning message'),
        ];

        yield 'deprecation suppression is ignored and triggered warning is not added to baseline' => [
            'source' => self::buildSource(
                ignoreSuppressionOfDeprecations: true,
                ignoreSuppressionOfPhpDeprecations: false,
                ignoreSuppressionOfErrors: false,
                ignoreSuppressionOfNotices: false,
                ignoreSuppressionOfPhpNotices: false,
                ignoreSuppressionOfWarnings: false,
                ignoreSuppressionOfPhpWarnings: false,
            ),
            'events'           => [$warning],
            'expectedBaseline' => new Baseline,
        ];

        $deprecationTriggeredEvent             = self::buildDeprecationTriggeredEvent('deprecation message', $anyExistingFileName, 10, true);
        $noticeTriggeredEvent                  = self::buildNoticeTriggeredEvent('notice triggered', $anyExistingFileName, 20, true);
        $phpDeprecationTriggeredEvent          = self::buildPhpDeprecationTriggeredEvent('php deprecation message', $anyExistingFileName, 30, true);
        $phpNoticeTriggeredEvent               = self::buildPhpNoticeTriggeredEvent('php notice message', $anyExistingFileName, 40, true);
        $phpWarningTriggeredEvent              = self::buildPhpWarningTriggeredEvent('php warning triggered', $anyExistingFileName, 50, true);
        $warningTriggeredEvent                 = self::buildWarningTriggeredEvent('warning message', $anyExistingFileName, 60, true);
        $nonSilentDeprecationTriggeredEvent    = self::buildDeprecationTriggeredEvent('non silent deprecation message', $anyExistingFileName, 15, false);
        $nonSilentNoticeTriggeredEvent         = self::buildNoticeTriggeredEvent('non silent notice triggered', $anyExistingFileName, 25, false);
        $nonSilentPhpDeprecationTriggeredEvent = self::buildPhpDeprecationTriggeredEvent('non silent php deprecation message', $anyExistingFileName, 35, false);
        $nonSilentPhpNoticeTriggeredEvent      = self::buildPhpNoticeTriggeredEvent('non silent php notice message', $anyExistingFileName, 45, false);
        $nonSilentPhpWarningTriggeredEvent     = self::buildPhpWarningTriggeredEvent('non silent php warning triggered', $anyExistingFileName, 55, false);
        $nonSilentWarningTriggeredEvent        = self::buildWarningTriggeredEvent('non silent warning message', $anyExistingFileName, 65, false);

        yield 'all suppressions are ignored and triggered events are added to baseline' => [
            'source' => self::buildSource(
                ignoreSuppressionOfDeprecations: true,
                ignoreSuppressionOfPhpDeprecations: true,
                ignoreSuppressionOfErrors: true,
                ignoreSuppressionOfNotices: true,
                ignoreSuppressionOfPhpNotices: true,
                ignoreSuppressionOfWarnings: true,
                ignoreSuppressionOfPhpWarnings: true,
            ),
            'events' => [
                $deprecationTriggeredEvent,
                $noticeTriggeredEvent,
                $phpDeprecationTriggeredEvent,
                $phpNoticeTriggeredEvent,
                $phpWarningTriggeredEvent,
                $warningTriggeredEvent,
                $nonSilentDeprecationTriggeredEvent,
                $nonSilentNoticeTriggeredEvent,
                $nonSilentPhpDeprecationTriggeredEvent,
                $nonSilentPhpNoticeTriggeredEvent,
                $nonSilentPhpWarningTriggeredEvent,
                $nonSilentWarningTriggeredEvent,
            ],
            'expectedBaseline' => self::buildBaselineWithIssues(
                [
                    Issue::from($anyExistingFileName, 10, null, 'deprecation message'),
                    Issue::from($anyExistingFileName, 20, null, 'notice triggered'),
                    Issue::from($anyExistingFileName, 30, null, 'php deprecation message'),
                    Issue::from($anyExistingFileName, 40, null, 'php notice message'),
                    Issue::from($anyExistingFileName, 50, null, 'php warning triggered'),
                    Issue::from($anyExistingFileName, 60, null, 'warning message'),
                    Issue::from($anyExistingFileName, 15, null, 'non silent deprecation message'),
                    Issue::from($anyExistingFileName, 25, null, 'non silent notice triggered'),
                    Issue::from($anyExistingFileName, 35, null, 'non silent php deprecation message'),
                    Issue::from($anyExistingFileName, 45, null, 'non silent php notice message'),
                    Issue::from($anyExistingFileName, 55, null, 'non silent php warning triggered'),
                    Issue::from($anyExistingFileName, 65, null, 'non silent warning message'),
                ],
            ),
        ];

        yield 'all suppressions are not ignored but non suppressed triggered events are added to baseline' => [
            'source' => self::buildSource(
                ignoreSuppressionOfDeprecations: false,
                ignoreSuppressionOfPhpDeprecations: false,
                ignoreSuppressionOfErrors: false,
                ignoreSuppressionOfNotices: false,
                ignoreSuppressionOfPhpNotices: false,
                ignoreSuppressionOfWarnings: false,
                ignoreSuppressionOfPhpWarnings: false,
            ),
            'events' => [
                $deprecationTriggeredEvent,
                $noticeTriggeredEvent,
                $phpDeprecationTriggeredEvent,
                $phpNoticeTriggeredEvent,
                $phpWarningTriggeredEvent,
                $warningTriggeredEvent,
                $nonSilentDeprecationTriggeredEvent,
                $nonSilentNoticeTriggeredEvent,
                $nonSilentPhpDeprecationTriggeredEvent,
                $nonSilentPhpNoticeTriggeredEvent,
                $nonSilentPhpWarningTriggeredEvent,
                $nonSilentWarningTriggeredEvent,
            ],
            'expectedBaseline' => self::buildBaselineWithIssues(
                [
                    Issue::from($anyExistingFileName, 15, null, 'non silent deprecation message'),
                    Issue::from($anyExistingFileName, 25, null, 'non silent notice triggered'),
                    Issue::from($anyExistingFileName, 35, null, 'non silent php deprecation message'),
                    Issue::from($anyExistingFileName, 45, null, 'non silent php notice message'),
                    Issue::from($anyExistingFileName, 55, null, 'non silent php warning triggered'),
                    Issue::from($anyExistingFileName, 65, null, 'non silent warning message'),
                ],
            ),
        ];
    }

    /**
     * @param list<DeprecationTriggered|NoticeTriggered|PhpDeprecationTriggered|PhpNoticeTriggered|PhpWarningTriggered|WarningTriggered> $events
     */
    #[DataProvider('provideIgnoreSuppressions')]
    public function testTestTriggeredIssue(
        Source $source,
        array $events,
        Baseline $expectedBaseline
    ): void {
        $generator = new Generator(
            new Facade,
            $source,
        );

        foreach ($events as $event) {
            $generator->testTriggeredIssue($event);
        }
        $this->assertEquals(
            $expectedBaseline,
            $generator->baseline(),
        );
    }

    private static function buildSource(
        bool $ignoreSuppressionOfDeprecations,
        bool $ignoreSuppressionOfPhpDeprecations,
        bool $ignoreSuppressionOfErrors,
        bool $ignoreSuppressionOfNotices,
        bool $ignoreSuppressionOfPhpNotices,
        bool $ignoreSuppressionOfWarnings,
        bool $ignoreSuppressionOfPhpWarnings,
    ): Source {
        return new Source(
            'baseline.xml',
            false,
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FileCollection::fromArray([]),
            false,
            false,
            false,
            $ignoreSuppressionOfDeprecations,
            $ignoreSuppressionOfPhpDeprecations,
            $ignoreSuppressionOfErrors,
            $ignoreSuppressionOfNotices,
            $ignoreSuppressionOfPhpNotices,
            $ignoreSuppressionOfWarnings,
            $ignoreSuppressionOfPhpWarnings,
        );
    }

    private static function buildTestMethod(): TestMethod
    {
        return new TestMethod(
            'ExampleTest',
            'testExampleFunction',
            'ExampleTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Example', 'exampleFunction'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }

    private static function buildTelemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime()),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000),
                new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
        );
    }

    private static function buildSingleIssueBaseline(
        string $file,
        int $line,
        string $description
    ): Baseline {
        $baseline = new Baseline;
        $baseline->add(
            Issue::from($file, $line, null, $description),
        );

        return $baseline;
    }

    /**
     * @param list<Issue> $issues
     */
    private static function buildBaselineWithIssues(
        array $issues
    ): Baseline {
        $baseline = new Baseline;

        foreach ($issues as $issue) {
            $baseline->add($issue);
        }

        return $baseline;
    }

    private static function buildDeprecationTriggeredEvent(
        string $message,
        string $fileName,
        int $lineNumber,
        bool $isSuppressed
    ): DeprecationTriggered {
        return new DeprecationTriggered(
            self::buildTelemetryInfo(),
            self::buildTestMethod(),
            $message,
            $fileName,
            $lineNumber,
            $isSuppressed,
            false,
            true,
        );
    }

    private static function buildNoticeTriggeredEvent(
        string $message,
        string $fileName,
        int $lineNumber,
        bool $isSuppressed
    ): NoticeTriggered {
        return new NoticeTriggered(
            self::buildTelemetryInfo(),
            self::buildTestMethod(),
            $message,
            $fileName,
            $lineNumber,
            $isSuppressed,
            false,
        );
    }

    private static function buildPhpDeprecationTriggeredEvent(
        string $message,
        string $fileName,
        int $lineNumber,
        bool $isSuppressed
    ): PhpDeprecationTriggered {
        return new PhpDeprecationTriggered(
            self::buildTelemetryInfo(),
            self::buildTestMethod(),
            $message,
            $fileName,
            $lineNumber,
            $isSuppressed,
            false,
            false,
        );
    }

    private static function buildWarningTriggeredEvent(
        string $message,
        string $fileName,
        int $lineNumber,
        bool $isSuppressed
    ): WarningTriggered {
        return new WarningTriggered(
            self::buildTelemetryInfo(),
            self::buildTestMethod(),
            $message,
            $fileName,
            $lineNumber,
            $isSuppressed,
            false,
        );
    }

    private static function buildPhpNoticeTriggeredEvent(
        string $message,
        string $fileName,
        int $lineNumber,
        bool $isSuppressed
    ): PhpNoticeTriggered {
        return new PhpNoticeTriggered(
            self::buildTelemetryInfo(),
            self::buildTestMethod(),
            $message,
            $fileName,
            $lineNumber,
            $isSuppressed,
            false,
        );
    }

    private static function buildPhpWarningTriggeredEvent(
        string $message,
        string $fileName,
        int $lineNumber,
        bool $isSuppressed
    ): PhpWarningTriggered {
        return new PhpWarningTriggered(
            self::buildTelemetryInfo(),
            self::buildTestMethod(),
            $message,
            $fileName,
            $lineNumber,
            $isSuppressed,
            false,
        );
    }
}
