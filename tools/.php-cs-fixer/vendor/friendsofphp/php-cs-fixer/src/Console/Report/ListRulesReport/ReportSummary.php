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
final class ReportSummary
{
    /**
     * @var list<FixerInterface>
     */
    private array $fixers;

    /**
     * @param list<FixerInterface> $fixers
     */
    public function __construct(array $fixers)
    {
        $this->fixers = $fixers;
    }

    /**
     * @return list<FixerInterface>
     */
    public function getFixers(): array
    {
        return $this->fixers;
    }
}
