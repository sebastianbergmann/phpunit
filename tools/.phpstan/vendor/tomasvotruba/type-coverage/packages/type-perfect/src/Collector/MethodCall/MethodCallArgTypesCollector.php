<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Collector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ExtendedMethodReflection;
use Rector\TypePerfect\Configuration;
use Rector\TypePerfect\Matcher\ClassMethodCallReferenceResolver;
use Rector\TypePerfect\Printer\CollectorMetadataPrinter;
use Rector\TypePerfect\ValueObject\MethodCallReference;

/**
 * @implements Collector<MethodCall, array{string, string}>
 */
final readonly class MethodCallArgTypesCollector implements Collector
{
    public function __construct(
        private ClassMethodCallReferenceResolver $classMethodCallReferenceResolver,
        private CollectorMetadataPrinter $collectorMetadataPrinter,
        private Configuration $configuration
    ) {
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return array{string, string}|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (! $this->configuration->isNarrowParamEnabled()) {
            return null;
        }

        if ($node->isFirstClassCallable() || $node->getArgs() === [] || ! $node->name instanceof Identifier) {
            return null;
        }

        $methodCalledOnType = $scope->getType($node->var);
        $methodReflection = $scope->getMethodReflection($methodCalledOnType, $node->name->name);
        if (! $methodReflection instanceof ExtendedMethodReflection) {
            return null;
        }

        $classMethodCallReference = $this->classMethodCallReferenceResolver->resolve($node, $scope, true);
        if (! $classMethodCallReference instanceof MethodCallReference) {
            return null;
        }

        $classMethodReference = $this->createClassMethodReference($classMethodCallReference);

        $stringArgTypesString = $this->collectorMetadataPrinter->printArgTypesAsString(
            $node,
            $methodReflection,
            $scope
        );
        return [$classMethodReference, $stringArgTypesString];
    }

    private function createClassMethodReference(MethodCallReference $classMethodCallReference): string
    {
        $className = $classMethodCallReference->getClass();
        $methodName = $classMethodCallReference->getMethod();

        return $className . '::' . $methodName;
    }
}
