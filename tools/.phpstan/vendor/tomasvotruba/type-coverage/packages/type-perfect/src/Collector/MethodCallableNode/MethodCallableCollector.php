<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Collector\MethodCallableNode;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\MethodCallableNode;
use Rector\TypePerfect\Configuration;
use Rector\TypePerfect\Matcher\ClassMethodCallReferenceResolver;
use Rector\TypePerfect\ValueObject\MethodCallReference;

/**
 * @implements Collector<MethodCallableNode, array<string>|null>
 *
 * PHPStan has special node for first class callables of MethodCall
 *
 * @see https://github.com/phpstan/phpstan-src/blob/511c1e435fb43b8eb0ac310e6aa3230147963790/src/Analyser/NodeScopeResolver.php#L1936
 */
final readonly class MethodCallableCollector implements Collector
{
    public function __construct(
        private ClassMethodCallReferenceResolver $classMethodCallReferenceResolver,
        private Configuration $configuration
    ) {
    }

    public function getNodeType(): string
    {
        return MethodCallableNode::class;
    }

    /**
     * @param MethodCallableNode $node
     * @return array{string}|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (! $this->configuration->isNarrowParamEnabled()) {
            return null;
        }

        $classMethodCallReference = $this->classMethodCallReferenceResolver->resolve($node, $scope, true);
        if (! $classMethodCallReference instanceof MethodCallReference) {
            return null;
        }

        $classMethodReference = $this->createClassMethodReference($classMethodCallReference);

        // special case that should skip everything
        return [$classMethodReference];
    }

    private function createClassMethodReference(MethodCallReference $classMethodCallReference): string
    {
        $className = $classMethodCallReference->getClass();
        $methodName = $classMethodCallReference->getMethod();

        return $className . '::' . $methodName;
    }
}
