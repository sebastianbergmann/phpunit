<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Analyzer\Analysis;

/**
 * @internal
 */
final class CaseAnalysis
{
    private int $colonIndex;

    public function __construct(int $colonIndex)
    {
        $this->colonIndex = $colonIndex;
    }

    public function getColonIndex(): int
    {
        return $this->colonIndex;
    }
}
