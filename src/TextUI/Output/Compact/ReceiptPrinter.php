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
use function array_map;
use function implode;
use function preg_match;
use function sprintf;
use function str_replace;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Runner\Receipt\Invocation;
use PHPUnit\Runner\Receipt\IssueCounts;
use PHPUnit\Runner\Receipt\Outcome;
use PHPUnit\TextUI\Output\Printer;

/**
 * Prints the receipt of the test run as the last record of the compact output.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ReceiptPrinter
{
    private Printer $printer;
    private Renderer $renderer;

    public function __construct(Printer $printer)
    {
        $this->printer  = $printer;
        $this->renderer = new Renderer($printer);
    }

    public function print(Invocation $invocation, Outcome $outcome): void
    {
        $this->printer->print(PHP_EOL . '--- RECEIPT' . PHP_EOL);

        $this->renderer->printLabelledLine('PHPUnit', $this->phpunit($invocation));
        $this->renderer->printLabelledLine('PHP', $invocation->phpVersion());
        $this->renderer->printLabelledLine('Arguments', $this->listOrNone(array_map($this->argument(...), $invocation->arguments()), ' '));
        $this->renderer->printLabelledLine('Working directory', $invocation->workingDirectory());
        $this->renderer->printLabelledLine('Configuration', $this->configuration($invocation));
        $this->renderer->printLabelledLine('Bootstrap', $this->listOrNone($invocation->bootstrapScripts(), ', '));
        $this->renderer->printLabelledLine('Extensions', $this->listOrNone($invocation->extensionClasses(), ', '));
        $this->renderer->printLabelledLine('Started', $this->time($invocation->startTime()));
        $this->renderer->printLabelledLine('Ended', $this->time($outcome->endTime()) . ' (' . $outcome->status()->value . ')');
        $this->renderer->printLabelledLine('Shell exit code', (string) $outcome->shellExitCode());
        $this->renderer->printLabelledLine('Tests', $this->tests($outcome));
        $this->renderer->printLabelledLine('Issues triggered by tests', $this->issues($outcome->issuesTriggeredByTests()));
        $this->renderer->printLabelledLine('Issues triggered by the test runner', $this->issues($outcome->issuesTriggeredByTestRunner()));
    }

    private function phpunit(Invocation $invocation): string
    {
        if (!$invocation->hasPhar()) {
            return $invocation->phpunitVersion();
        }

        if (!$invocation->hasPharChecksum()) {
            return sprintf(
                '%s (PHAR: %s)',
                $invocation->phpunitVersion(),
                $invocation->phar(),
            );
        }

        return sprintf(
            '%s (PHAR: %s, SHA-256: %s)',
            $invocation->phpunitVersion(),
            $invocation->phar(),
            $invocation->pharChecksum(),
        );
    }

    private function configuration(Invocation $invocation): string
    {
        if (!$invocation->hasConfigurationFile()) {
            return '(none)';
        }

        if (!$invocation->hasConfigurationFileChecksum()) {
            return $invocation->configurationFile();
        }

        return sprintf(
            '%s (SHA-256: %s)',
            $invocation->configurationFile(),
            $invocation->configurationFileChecksum(),
        );
    }

    /**
     * An argument that is not made of characters that are safe in every shell
     * is quoted, so that it can be told where one argument ends and where the
     * next one begins.
     */
    private function argument(string $argument): string
    {
        if (preg_match('/^[A-Za-z0-9_.,:=\/\\\\@%+-]+$/', $argument) === 1) {
            return $argument;
        }

        return "'" . str_replace("'", "'\\''", $argument) . "'";
    }

    /**
     * @param list<string>     $elements
     * @param non-empty-string $separator
     */
    private function listOrNone(array $elements, string $separator): string
    {
        if ($elements === []) {
            return '(none)';
        }

        return implode($separator, $elements);
    }

    private function time(DateTimeImmutable $time): string
    {
        return $time->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:sP');
    }

    private function tests(Outcome $outcome): string
    {
        $counts = [
            sprintf('%d run', $outcome->numberOfTestsRun()),
            $this->count($outcome->numberOfAssertions(), 'assertion'),
        ];

        foreach ([
            'errored'    => $outcome->numberOfErroredTests(),
            'failed'     => $outcome->numberOfFailedTests(),
            'skipped'    => $outcome->numberOfSkippedTests(),
            'incomplete' => $outcome->numberOfIncompleteTests(),
            'risky'      => $outcome->numberOfRiskyTests(),
        ] as $label => $count) {
            if ($count > 0) {
                $counts[] = sprintf('%d %s', $count, $label);
            }
        }

        return implode(', ', $counts);
    }

    private function issues(IssueCounts $issueCounts): string
    {
        $counts = [];

        foreach ([
            'error'               => $issueCounts->errors(),
            'deprecation'         => $issueCounts->deprecations(),
            'notice'              => $issueCounts->notices(),
            'warning'             => $issueCounts->warnings(),
            'PHPUnit error'       => $issueCounts->phpunitErrors(),
            'PHPUnit deprecation' => $issueCounts->phpunitDeprecations(),
            'PHPUnit notice'      => $issueCounts->phpunitNotices(),
            'PHPUnit warning'     => $issueCounts->phpunitWarnings(),
        ] as $label => $count) {
            if ($count > 0) {
                $counts[] = $this->count($count, $label);
            }
        }

        return $this->listOrNone($counts, ', ');
    }

    /**
     * @param non-empty-string $singular
     */
    private function count(int $count, string $singular): string
    {
        if ($count === 1) {
            return '1 ' . $singular;
        }

        return sprintf('%d %ss', $count, $singular);
    }
}
