<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage;

use TomasVotruba\TypeCoverage\ValueObject\TypeCountAndMissingTypes;

final class CollectorDataNormalizer
{
    /**
     * @param array<string, array<array{0: int, 1: array<string, int>}>> $collectorDataByPath
     */
    public function normalize(array $collectorDataByPath): TypeCountAndMissingTypes
    {
        $totalCount = 0;
        $missingCount = 0;

        $missingTypeLinesByFilePath = [];

        foreach ($collectorDataByPath as $filePath => $typeCoverageData) {
            foreach ($typeCoverageData as $nestedData) {
                $totalCount += $nestedData[0];

                $missingCount += count($nestedData[1]);

                $missingTypeLinesByFilePath[$filePath] = array_merge(
                    $missingTypeLinesByFilePath[$filePath] ?? [],
                    $nestedData[1]
                );
            }
        }

        return new TypeCountAndMissingTypes($totalCount, $missingCount, $missingTypeLinesByFilePath);
    }
}
