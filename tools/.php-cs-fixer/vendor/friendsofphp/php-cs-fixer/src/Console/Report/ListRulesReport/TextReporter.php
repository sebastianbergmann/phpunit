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

namespace PhpCsFixer\Console\Report\ListRulesReport;

use PhpCsFixer\Fixer\FixerInterface;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
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
        $fixers = $reportSummary->getFixers();

        usort($fixers, static fn (FixerInterface $a, FixerInterface $b): int => $a->getName() <=> $b->getName());

        $output = '';

        foreach ($fixers as $i => $fixer) {
            $output .= \sprintf('%3d) %s', $i + 1, $fixer->getName()).\PHP_EOL.'       '.$fixer->getDefinition()->getSummary().\PHP_EOL;

            if ($fixer->isRisky()) {
                $output .= '       Rule is risky.'.\PHP_EOL;
            }
        }

        return $output;
    }
}
