<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Console\Report\FixReport;

use PhpCsFixer\Differ\DiffConsoleFormatter;

/**
 * @author Boris Gorbylev <ekho@ekho.name>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TextReporter implements ReporterInterface
{
    public function getFormat(): string
    {
        return 'txt';
    }

    public function generate(ReportSummary $reportSummary): string
    {
        $output = '';

        $identifiedFiles = 0;
        foreach ($reportSummary->getChanged() as $file => $fixResult) {
            ++$identifiedFiles;
            $output .= \sprintf('%4d) %s', $identifiedFiles, $file);

            if ($reportSummary->shouldAddAppliedFixers()) {
                $output .= $this->getAppliedFixers(
                    $reportSummary->isDecoratedOutput(),
                    $fixResult['appliedFixers'],
                );
            }

            $output .= $this->getDiff($reportSummary->isDecoratedOutput(), $fixResult['diff']);
            $output .= \PHP_EOL;
        }

        return $output.$this->getFooter(
            $reportSummary->getTime(),
            $identifiedFiles,
            $reportSummary->getFilesCount(),
            $reportSummary->getMemory(),
            $reportSummary->isDryRun(),
        );
    }

    /**
     * @param list<string> $appliedFixers
     */
    private function getAppliedFixers(bool $isDecoratedOutput, array $appliedFixers): string
    {
        return \sprintf(
            $isDecoratedOutput ? ' (<comment>%s</comment>)' : ' (%s)',
            implode(', ', $appliedFixers),
        );
    }

    private function getDiff(bool $isDecoratedOutput, string $diff): string
    {
        if ('' === $diff) {
            return '';
        }

        $diffFormatter = new DiffConsoleFormatter($isDecoratedOutput, \sprintf(
            '<comment>      ---------- begin diff ----------</comment>%s%%s%s<comment>      ----------- end diff -----------</comment>',
            \PHP_EOL,
            \PHP_EOL,
        ));

        return \PHP_EOL.$diffFormatter->format($diff).\PHP_EOL;
    }

    private function getFooter(int $time, int $identifiedFiles, int $files, int $memory, bool $isDryRun): string
    {
        return \PHP_EOL.\sprintf(
            '%s %d of %d %s in %.3f seconds, %.2f MB memory used'.\PHP_EOL,
            $isDryRun ? 'Found' : 'Fixed',
            $identifiedFiles,
            $files,
            $isDryRun ? 'files that can be fixed' : 'files',
            $time / 1_000,
            $memory / 1_024 / 1_024,
        );
    }
}
