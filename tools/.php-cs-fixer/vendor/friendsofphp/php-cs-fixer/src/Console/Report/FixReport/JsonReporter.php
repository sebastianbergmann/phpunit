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

use PhpCsFixer\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * @author Boris Gorbylev <ekho@ekho.name>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class JsonReporter implements ReporterInterface
{
    public function getFormat(): string
    {
        return 'json';
    }

    public function generate(ReportSummary $reportSummary): string
    {
        $jsonFiles = [];

        foreach ($reportSummary->getChanged() as $file => $fixResult) {
            $jsonFile = ['name' => $file];

            if ($reportSummary->shouldAddAppliedFixers()) {
                $jsonFile['appliedFixers'] = $fixResult['appliedFixers'];
            }

            if ('' !== $fixResult['diff']) {
                $jsonFile['diff'] = $fixResult['diff'];
            }

            $jsonFiles[] = $jsonFile;
        }

        $json = [
            'about' => Application::getAbout(),
            'files' => $jsonFiles,
            'time' => [
                'total' => round($reportSummary->getTime() / 1_000, 3),
            ],
            'memory' => round($reportSummary->getMemory() / 1_024 / 1_024, 3),
        ];

        $json = json_encode($json, \JSON_THROW_ON_ERROR);

        return $reportSummary->isDecoratedOutput() ? OutputFormatter::escape($json) : $json;
    }
}
