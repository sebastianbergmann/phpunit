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
final class JsonReporter implements ReporterInterface
{
    public function getFormat(): string
    {
        return 'json';
    }

    public function generate(ReportSummary $reportSummary): string
    {
        $sets = $reportSummary->getSets();

        usort($sets, static fn (RuleSetDefinitionInterface $a, RuleSetDefinitionInterface $b): int => $a->getName() <=> $b->getName());

        $json = ['sets' => []];

        foreach ($sets as $set) {
            $setName = $set->getName();
            $json['sets'][$setName] = [
                'description' => $set->getDescription(),
                'isRisky' => $set->isRisky(),
                'name' => $setName,
            ];
        }

        return json_encode($json, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
    }
}
