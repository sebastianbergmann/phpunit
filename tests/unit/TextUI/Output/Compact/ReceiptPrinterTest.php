<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Compact;

use const PHP_EOL;
use function implode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Receipt\Invocation;
use PHPUnit\Runner\Receipt\IssueCounts;
use PHPUnit\Runner\Receipt\Outcome;
use PHPUnit\Runner\Receipt\Status;
use PHPUnit\TextUI\Output\Printer;

#[CoversClass(ReceiptPrinter::class)]
#[UsesClass(Renderer::class)]
#[UsesClass(Invocation::class)]
#[UsesClass(Outcome::class)]
#[UsesClass(IssueCounts::class)]
#[Small]
#[Group('textui')]
#[Group('textui/output')]
#[Group('textui/output/compact')]
final class ReceiptPrinterTest extends TestCase
{
    public function testPrintsReceiptOfTestRunThatHasNothingOptional(): void
    {
        $this->assertSame(
            implode(
                PHP_EOL,
                [
                    '',
                    '--- RECEIPT',
                    'PHPUnit: 13.5.0',
                    'PHP: 8.5.0',
                    'Arguments: (none)',
                    'Working directory: /path/to/project',
                    'Configuration: (none)',
                    'Bootstrap: (none)',
                    'Extensions: (none)',
                    'Started: 2026-12-04T10:00:00+00:00',
                    'Ended: 2026-12-04T10:00:02+00:00 (completed)',
                    'Shell exit code: 0',
                    'Tests: 0 run, 0 assertions',
                    'Issues triggered by tests: (none)',
                    'Issues triggered by the test runner: (none)',
                    '',
                ],
            ),
            $this->print(
                $this->invocation(),
                $this->outcome(),
            ),
        );
    }

    public function testPrintsReceiptOfTestRunThatHasEverythingOptional(): void
    {
        $this->assertSame(
            implode(
                PHP_EOL,
                [
                    '',
                    '--- RECEIPT',
                    'PHPUnit: 13.5.0 (PHAR: /path/to/phpunit.phar, SHA-256: checksum-of-phar)',
                    'PHP: 8.5.0',
                    'Arguments: --compact --timeout 60 tests',
                    'Working directory: /path/to/project',
                    'Configuration: /path/to/project/phpunit.xml (SHA-256: checksum-of-configuration-file)',
                    'Bootstrap: /path/to/project/tests/bootstrap.php, /path/to/project/tests/unit/bootstrap.php',
                    'Extensions: Vendor\Extension\Bootstrap, Vendor\AnotherExtension\Bootstrap',
                    'Started: 2026-12-04T10:00:00+00:00',
                    'Ended: 2026-12-04T10:00:02+00:00 (stopped)',
                    'Shell exit code: 2',
                    'Tests: 12 run, 30 assertions, 2 errored, 3 failed, 4 skipped, 5 incomplete, 6 risky',
                    'Issues triggered by tests: 2 errors, 3 deprecations, 4 notices, 5 warnings, 6 PHPUnit errors, 7 PHPUnit deprecations, 8 PHPUnit notices, 9 PHPUnit warnings',
                    'Issues triggered by the test runner: 1 error, 1 deprecation, 1 notice, 1 warning, 1 PHPUnit error, 1 PHPUnit deprecation, 1 PHPUnit notice, 1 PHPUnit warning',
                    '',
                ],
            ),
            $this->print(
                $this->invocation(
                    phar: '/path/to/phpunit.phar',
                    pharChecksum: 'checksum-of-phar',
                    arguments: ['--compact', '--timeout', '60', 'tests'],
                    configurationFile: '/path/to/project/phpunit.xml',
                    configurationFileChecksum: 'checksum-of-configuration-file',
                    bootstrapScripts: ['/path/to/project/tests/bootstrap.php', '/path/to/project/tests/unit/bootstrap.php'],
                    extensionClasses: ['Vendor\Extension\Bootstrap', 'Vendor\AnotherExtension\Bootstrap'],
                ),
                new Outcome(
                    new DateTimeImmutable('2026-12-04T10:00:02+00:00'),
                    Status::Stopped,
                    2,
                    12,
                    30,
                    2,
                    3,
                    4,
                    5,
                    6,
                    new IssueCounts(2, 3, 4, 5, 6, 7, 8, 9),
                    new IssueCounts(1, 1, 1, 1, 1, 1, 1, 1),
                ),
            ),
        );
    }

    public function testPrintsSingularForOneAssertion(): void
    {
        $this->assertStringContainsString(
            PHP_EOL . 'Tests: 1 run, 1 assertion' . PHP_EOL,
            $this->print(
                $this->invocation(),
                new Outcome(
                    new DateTimeImmutable('2026-12-04T10:00:02+00:00'),
                    Status::Completed,
                    0,
                    1,
                    1,
                    0,
                    0,
                    0,
                    0,
                    0,
                    new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0),
                    new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0),
                ),
            ),
        );
    }

    public function testPrintsPharWithoutChecksum(): void
    {
        $this->assertStringContainsString(
            PHP_EOL . 'PHPUnit: 13.5.0 (PHAR: /path/to/phpunit.phar)' . PHP_EOL,
            $this->print(
                $this->invocation(phar: '/path/to/phpunit.phar'),
                $this->outcome(),
            ),
        );
    }

    public function testPrintsConfigurationFileWithoutChecksum(): void
    {
        $this->assertStringContainsString(
            PHP_EOL . 'Configuration: /path/to/project/phpunit.xml' . PHP_EOL,
            $this->print(
                $this->invocation(configurationFile: '/path/to/project/phpunit.xml'),
                $this->outcome(),
            ),
        );
    }

    public function testPrintsTimesInUtc(): void
    {
        $output = $this->print(
            $this->invocation(startTime: new DateTimeImmutable('2026-12-04T11:00:00+01:00')),
            $this->outcome(new DateTimeImmutable('2026-12-04T05:00:02-05:00')),
        );

        $this->assertStringContainsString(PHP_EOL . 'Started: 2026-12-04T10:00:00+00:00' . PHP_EOL, $output);
        $this->assertStringContainsString(PHP_EOL . 'Ended: 2026-12-04T10:00:02+00:00 (completed)' . PHP_EOL, $output);
    }

    public function testQuotesArgumentsThatAreNotSafeInEveryShell(): void
    {
        $this->assertStringContainsString(
            PHP_EOL . 'Arguments: --filter \'two words\' \'\' \'it\'\\\'\'s\' C:\\tests\\unit --columns=80' . PHP_EOL,
            $this->print(
                $this->invocation(arguments: ['--filter', 'two words', '', 'it\'s', 'C:\\tests\\unit', '--columns=80']),
                $this->outcome(),
            ),
        );
    }

    public function testKeepsEachValueOnTheLineOfItsLabel(): void
    {
        $output = $this->print(
            $this->invocation(
                arguments: ["--filter\n--- RECEIPT\nShell exit code: 0"],
                workingDirectory: "/path/to\nproject\x1B[2J",
            ),
            $this->outcome(),
        );

        $this->assertStringContainsString(
            PHP_EOL . 'Arguments: \'--filter\u{000A}--- RECEIPT\u{000A}Shell exit code: 0\'' . PHP_EOL,
            $output,
        );

        $this->assertStringContainsString(
            PHP_EOL . 'Working directory: /path/to\u{000A}project\u{001B}[2J' . PHP_EOL,
            $output,
        );
    }

    private function print(Invocation $invocation, Outcome $outcome): string
    {
        $output  = '';
        $printer = $this->createStub(Printer::class);

        $printer
            ->method('print')
            ->willReturnCallback(
                static function (string $buffer) use (&$output): void
                {
                    $output .= $buffer;
                },
            );

        new ReceiptPrinter($printer)->print($invocation, $outcome);

        return $output;
    }

    /**
     * @param ?non-empty-string      $phar
     * @param ?non-empty-string      $pharChecksum
     * @param list<string>           $arguments
     * @param ?non-empty-string      $configurationFile
     * @param ?non-empty-string      $configurationFileChecksum
     * @param list<non-empty-string> $bootstrapScripts
     * @param list<non-empty-string> $extensionClasses
     */
    private function invocation(?string $phar = null, ?string $pharChecksum = null, array $arguments = [], string $workingDirectory = '/path/to/project', ?string $configurationFile = null, ?string $configurationFileChecksum = null, array $bootstrapScripts = [], array $extensionClasses = [], DateTimeImmutable $startTime = new DateTimeImmutable('2026-12-04T10:00:00+00:00')): Invocation
    {
        return new Invocation(
            '13.5.0',
            $phar,
            $pharChecksum,
            '8.5.0',
            $arguments,
            $workingDirectory,
            $configurationFile,
            $configurationFileChecksum,
            $bootstrapScripts,
            $extensionClasses,
            $startTime,
        );
    }

    private function outcome(DateTimeImmutable $endTime = new DateTimeImmutable('2026-12-04T10:00:02+00:00')): Outcome
    {
        return new Outcome(
            $endTime,
            Status::Completed,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0),
            new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0),
        );
    }
}
