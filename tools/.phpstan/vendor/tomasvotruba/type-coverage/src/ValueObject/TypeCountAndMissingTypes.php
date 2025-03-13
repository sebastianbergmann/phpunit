<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage\ValueObject;

final class TypeCountAndMissingTypes
{
    /**
     * @readonly
     */
    private int $totalCount;

    /**
     * @readonly
     */
    private int $missingCount;

    /**
     * @var array<string, int[]>
     * @readonly
     */
    private array $missingTypeLinesByFilePath;

    /**
     * @param array<string, int[]> $missingTypeLinesByFilePath
     */
    public function __construct(int $totalCount, int $missingCount, array $missingTypeLinesByFilePath)
    {
        $this->totalCount = $totalCount;
        $this->missingCount = $missingCount;
        $this->missingTypeLinesByFilePath = $missingTypeLinesByFilePath;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getFilledCount(): int
    {
        return $this->totalCount - $this->missingCount;
    }

    /**
     * @return array<string, int[]>
     */
    public function getMissingTypeLinesByFilePath(): array
    {
        return $this->missingTypeLinesByFilePath;
    }

    public function getCoveragePercentage(): float
    {
        if ($this->totalCount === 0) {
            return 100.0;
        }

        $relative = 100 * ($this->getTypedCount() / $this->totalCount);

        // round down with one decimal, to make error message clear that required value is not reached yet
        return floor($relative * 10) / 10;
    }

    private function getTypedCount(): int
    {
        $missingCount = 0;

        foreach ($this->missingTypeLinesByFilePath as $missingTypeLines) {
            $missingCount += count($missingTypeLines);
        }

        return $this->totalCount - $missingCount;
    }
}
