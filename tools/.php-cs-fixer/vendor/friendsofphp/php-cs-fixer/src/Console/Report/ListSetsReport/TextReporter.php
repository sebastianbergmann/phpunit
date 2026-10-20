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

namespace PhpCsFixer\Console\Report\ListSetsReport;

use PhpCsFixer\RuleSet\RuleSetDefinitionInterface;

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
        $sets = $reportSummary->getSets();

        usort($sets, static fn (RuleSetDefinitionInterface $a, RuleSetDefinitionInterface $b): int => $a->getName() <=> $b->getName());

        $output = '';

        foreach ($sets as $i => $set) {
            $output .= \sprintf('%2d) %s', $i + 1, $set->getName()).\PHP_EOL.'      '.$set->getDescription().\PHP_EOL;

            if ($set->isRisky()) {
                $output .= '      Set contains risky rules.'.\PHP_EOL;
            }
        }

        return $output;
    }
}
