<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use TomasVotruba\TypeCoverage\Collectors\DeclareCollector;
use TomasVotruba\TypeCoverage\Configuration;
use TomasVotruba\TypeCoverage\Configuration\ScopeConfigurationResolver;

/**
 * @see \TomasVotruba\TypeCoverage\Tests\Rules\DeclareCoverageRule\DeclareCoverageRuleTest
 *
 * @implements Rule<CollectedDataNode>
 */
final class DeclareCoverageRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Out of %d possible declare(strict_types=1), only %d - %.1f %% actually have it. Add more declares to get over %s %%';

    /**
     * @readonly
     */
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
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

        $requiredDeclareLevel = $this->configuration->getRequiredDeclareLevel();

        $declareCollector = $node->get(DeclareCollector::class);
        $totalPossibleDeclares = count($declareCollector);

        $coveredDeclares = 0;
        $notCoveredDeclareFilePaths = [];

        foreach ($declareCollector as $fileName => $data) {
            // has declares
            if ($data === [true]) {
                ++$coveredDeclares;
            } else {
                $notCoveredDeclareFilePaths[] = $fileName;
            }
        }

        $declareCoverage = ($coveredDeclares / $totalPossibleDeclares) * 100;

        if ($this->configuration->showOnlyMeasure()) {
            $errorMessage = sprintf(
                'Strict declares coverage is %.1f %% out of %d possible',
                $declareCoverage,
                $totalPossibleDeclares
            );
            return [RuleErrorBuilder::message($errorMessage)->build()];
        }

        // not enabled
        if ($requiredDeclareLevel === 0) {
            return [];
        }

        // nothing to handle
        if ($totalPossibleDeclares === 0) {
            return [];
        }

        // we meet the limit, all good
        if ($declareCoverage >= $requiredDeclareLevel) {
            return [];
        }

        $ruleErrors = [];
        foreach ($notCoveredDeclareFilePaths as $notCoveredDeclareFilePath) {
            $errorMessage = sprintf(
                self::ERROR_MESSAGE,
                $totalPossibleDeclares,
                $coveredDeclares,
                $declareCoverage,
                $requiredDeclareLevel,
            );

            $ruleErrors[] = RuleErrorBuilder::message($errorMessage)->file($notCoveredDeclareFilePath)->build();
        }

        return $ruleErrors;
    }
}
