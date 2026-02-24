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
use TomasVotruba\TypeCoverage\Collectors\ConstantTypeDeclarationCollector;
use TomasVotruba\TypeCoverage\Configuration;
use TomasVotruba\TypeCoverage\Configuration\ScopeConfigurationResolver;
use TomasVotruba\TypeCoverage\Formatter\TypeCoverageFormatter;

/**
 * @see \TomasVotruba\TypeCoverage\Tests\Rules\ConstantTypeCoverageRule\ConstantTypeCoverageRuleTest
 *
 * @implements Rule<CollectedDataNode>
 */
final class ConstantTypeCoverageRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Out of %d possible constant types, only %d - %.1f %% actually have it. Add more constant types to get over %s %%';

    /**
     * @var string
     */
    private const IDENTIFIER = 'typeCoverage.constantTypeCoverage';

    /**
     * @readonly
     */
    private TypeCoverageFormatter $typeCoverageFormatter;

    /**
     * @readonly
     */
    private Configuration $configuration;

    /**
     * @readonly
     */
    private CollectorDataNormalizer $collectorDataNormalizer;

    public function __construct(
        TypeCoverageFormatter $typeCoverageFormatter,
        Configuration $configuration,
        CollectorDataNormalizer $collectorDataNormalizer
    ) {
        $this->typeCoverageFormatter = $typeCoverageFormatter;
        $this->configuration = $configuration;
        $this->collectorDataNormalizer = $collectorDataNormalizer;
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
        // enable only on PHP 8.3+
        if (PHP_VERSION_ID < 80300) {
            return [];
        }

        // if only subpaths are analysed, skip as data will be false positive
        if (! ScopeConfigurationResolver::areFullPathsAnalysed($scope)) {
            return [];
        }

        $constantTypeDeclarationCollector = $node->get(ConstantTypeDeclarationCollector::class);
        $typeCountAndMissingTypes = $this->collectorDataNormalizer->normalize($constantTypeDeclarationCollector);

        if ($this->configuration->showOnlyMeasure()) {
            $errorMessage = sprintf(
                'Class constant type coverage is %.1f %% out of %d possible',
                $typeCountAndMissingTypes->getCoveragePercentage(),
                $typeCountAndMissingTypes->getTotalCount()
            );

            return [RuleErrorBuilder::message($errorMessage)->build()];
        }

        if (! $this->configuration->isConstantTypeCoverageEnabled()) {
            return [];
        }

        return $this->typeCoverageFormatter->formatErrors(
            self::ERROR_MESSAGE,
            self::IDENTIFIER,
            $this->configuration->getRequiredConstantTypeLevel(),
            $typeCountAndMissingTypes
        );
    }
}
