<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage;

use TomasVotruba\TypeCoverage\ValueObject\TypeCountAndMissingTypes;

final class CollectorDataNormalizer
{
    /**
     * @param array<string, array<array{0: int, 1: array<int, int>, 2?: string|null}>> $collectorDataByPath
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

                // if the node is from a trait, route the error to the trait file
                // instead of the using-class file, so lines match the actual source
                $effectiveFilePath = $nestedData[2] ?? $filePath;

                $missingTypeLinesByFilePath[$effectiveFilePath] = array_merge(
                    $missingTypeLinesByFilePath[$effectiveFilePath] ?? [],
                    $nestedData[1]
                );
            }
        }

        return new TypeCountAndMissingTypes($totalCount, $missingCount, $missingTypeLinesByFilePath);
    }
}
