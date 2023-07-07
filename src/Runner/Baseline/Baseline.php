<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Baseline;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Baseline
{
    /**
     * @psalm-var array<string, array<positive-int, list<Issue>>>
     */
    private array $issues = [];

    public function add(Issue $entry): void
    {
        if (!isset($this->issues[$entry->file()])) {
            $this->issues[$entry->file()] = [];
        }

        if (!isset($this->issues[$entry->file()][$entry->line()])) {
            $this->issues[$entry->file()][$entry->line()] = [];
        }

        $this->issues[$entry->file()][$entry->line()][] = $entry;
    }

    public function has(Issue $entry): bool
    {
        if (!isset($this->issues[$entry->file()][$entry->line()])) {
            return false;
        }

        foreach ($this->issues[$entry->file()][$entry->line()] as $_entry) {
            if ($_entry->equals($entry)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @psalm-return array<string, array<positive-int, list<Issue>>>
     */
    public function groupedByFileAndLine(): array
    {
        return $this->issues;
    }
}
