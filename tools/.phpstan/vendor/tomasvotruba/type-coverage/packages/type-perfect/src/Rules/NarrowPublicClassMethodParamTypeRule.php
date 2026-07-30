<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Rector\TypePerfect\Collector\ClassMethod\PublicClassMethodParamTypesCollector;
use Rector\TypePerfect\Collector\MethodCall\MethodCallArgTypesCollector;
use Rector\TypePerfect\Collector\MethodCallableNode\MethodCallableCollector;
use Rector\TypePerfect\Configuration;
use Rector\TypePerfect\Enum\Types\ResolvedTypes;

/**
 * @see \Rector\TypePerfect\Tests\Rules\NarrowPublicClassMethodParamTypeRule\NarrowPublicClassMethodParamTypeRuleTest
 *
 * @implements Rule<CollectedDataNode>
 */
final readonly class NarrowPublicClassMethodParamTypeRule implements Rule
{
    public const string ERROR_MESSAGE = 'Parameters should have "%s" types as the only types passed to this method';

    public function __construct(
        private Configuration $configuration
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
        if (! $this->configuration->isNarrowParamEnabled()) {
            return [];
        }

        $publicClassMethodCollector = $node->get(PublicClassMethodParamTypesCollector::class);
        $classMethodReferenceToArgTypes = $this->resolveClassMethodReferenceToArgTypes($node);

        $ruleErrors = [];

        foreach ($publicClassMethodCollector as $filePath => $declarations) {
            foreach ($declarations as [$className, $methodName, $paramTypesString, $line]) {
                $uniqueCollectedArgTypesString = $this->resolveUniqueArgTypesString(
                    $classMethodReferenceToArgTypes,
                    $className,
                    $methodName
                );

                if ($uniqueCollectedArgTypesString === null) {
                    continue;
                }

                if ($paramTypesString === $uniqueCollectedArgTypesString) {
                    continue;
                }

                $ruleErrors[] = RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $uniqueCollectedArgTypesString))
                    ->identifier('typePerfect.narrowPublicClassMethodParamType')
                    ->file($filePath)
                    ->line($line)
                    ->build();
            }
        }

        return $ruleErrors;
    }

    /**
     * @return array<string, string[]>
     */
    private function resolveClassMethodReferenceToArgTypes(CollectedDataNode $collectedDataNode): array
    {
        $methodCallArgTypesByFilePath = $collectedDataNode->get(MethodCallArgTypesCollector::class);

        // these should be skipped completely, as we have no idea what is being passed there
        $methodCallablesByFilePath = $collectedDataNode->get(MethodCallableCollector::class);

        $methodFirstClassCallables = $this->flattenCollectedData($methodCallablesByFilePath);

        // group call references and types
        $classMethodReferenceToTypes = [];

        foreach ($methodCallArgTypesByFilePath as $methodCallArgTypes) {
            foreach ($methodCallArgTypes as [$classMethodReference, $argTypesString]) {
                // skip it
                if (in_array($classMethodReference, $methodFirstClassCallables, true)) {
                    continue;
                }

                $classMethodReferenceToTypes[$classMethodReference][] = $argTypesString;
            }
        }

        // resolve unique values
        foreach ($classMethodReferenceToTypes as $key => $value) {
            $classMethodReferenceToTypes[$key] = array_unique($value);
        }

        return $classMethodReferenceToTypes;
    }

    /**
     * @param array<string, string[]> $classMethodReferenceToArgTypes
     */
    private function resolveUniqueArgTypesString(
        array $classMethodReferenceToArgTypes,
        string $className,
        string $methodName
    ): ?string {
        $currentClassMethodReference = $className . '::' . $methodName;

        $collectedArgTypes = $classMethodReferenceToArgTypes[$currentClassMethodReference] ?? null;
        if ($collectedArgTypes === null) {
            return null;
        }

        // we need exactly one type
        if (count($collectedArgTypes) !== 1) {
            return null;
        }

        // one of the arg types could not be resolved, we're not sure
        if (in_array(ResolvedTypes::UNKNOWN_TYPES, $collectedArgTypes, true)) {
            return null;
        }

        return $collectedArgTypes[0];
    }

    /**
     * @param array<string, list<(array<string>|null)>> $methodCallablesByFilePath
     * @return string[]
     */
    private function flattenCollectedData(array $methodCallablesByFilePath): array
    {
        $methodFirstClassCallables = [];
        foreach ($methodCallablesByFilePath as $methodCallables) {
            foreach ($methodCallables as $methodCallable) {
                // the collector returns null for nodes it cannot resolve
                if (! isset($methodCallable[0])) {
                    continue;
                }

                $methodFirstClassCallables[] = $methodCallable[0];
            }
        }

        return $methodFirstClassCallables;
    }
}
