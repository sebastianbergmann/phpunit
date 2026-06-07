<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use TomasVotruba\TypeCoverage\CollectorDataNormalizer;
use TomasVotruba\TypeCoverage\Collectors\PropertyTypeDeclarationCollector;
use TomasVotruba\TypeCoverage\Configuration;
use TomasVotruba\TypeCoverage\Configuration\ScopeConfigurationResolver;
use TomasVotruba\TypeCoverage\Formatter\TypeCoverageFormatter;

/**
 * @see \TomasVotruba\TypeCoverage\Tests\Rules\PropertyTypeCoverageRule\PropertyTypeCoverageRuleTest
 *
 * @implements Rule<CollectedDataNode>
 */
final readonly class PropertyTypeCoverageRule implements Rule
{
    public const string ERROR_MESSAGE = 'Out of %d possible property types, only %d - %.1f %% actually have it. Add more property types to get over %s %%';

    private const string IDENTIFIER = 'typeCoverage.propertyTypeCoverage';

    public function __construct(
        private TypeCoverageFormatter $typeCoverageFormatter,
        private Configuration $configuration,
        private CollectorDataNormalizer $collectorDataNormalizer,
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @param CollectedDataNode $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // if only subpaths are analysed, skip as data will be false positive
        if (! ScopeConfigurationResolver::areFullPathsAnalysed($scope)) {
            return [];
        }

        $propertyTypeDeclarationCollector = $node->get(PropertyTypeDeclarationCollector::class);
        $typeCountAndMissingTypes = $this->collectorDataNormalizer->normalize($propertyTypeDeclarationCollector);

        if ($this->configuration->showOnlyMeasure()) {
            $errorMessage = sprintf(
                'Property type coverage is %.1f %% out of %d possible',
                $typeCountAndMissingTypes->getCoveragePercentage(),
                $typeCountAndMissingTypes->getTotalCount()
            );

            return [RuleErrorBuilder::message($errorMessage)->build()];
        }

        if ($this->configuration->getRequiredPropertyTypeLevel() === 0) {
            return [];
        }

        return $this->typeCoverageFormatter->formatErrors(
            self::ERROR_MESSAGE,
            self::IDENTIFIER,
            $this->configuration->getRequiredPropertyTypeLevel(),
            $typeCountAndMissingTypes
        );
    }
}
