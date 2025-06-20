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
final class SwitchAnalysis
{
    private int $casesStart;
    private int $casesEnd;

    /** @var list<CaseAnalysis> */
    private array $cases = [];

    /**
     * @param list<CaseAnalysis> $cases
     */
    public function __construct(int $casesStart, int $casesEnd, array $cases)
    {
        $this->casesStart = $casesStart;
        $this->casesEnd = $casesEnd;
        $this->cases = $cases;
    }

    public function getCasesStart(): int
    {
        return $this->casesStart;
    }

    public function getCasesEnd(): int
    {
        return $this->casesEnd;
    }

    /**
     * @return list<CaseAnalysis>
     */
    public function getCases(): array
    {
        return $this->cases;
    }
}
