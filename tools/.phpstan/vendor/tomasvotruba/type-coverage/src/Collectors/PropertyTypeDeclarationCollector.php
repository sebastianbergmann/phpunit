<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage\Collectors;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;

/**
 * @see \TomasVotruba\TypeCoverage\Rules\PropertyTypeCoverageRule
 */
final class PropertyTypeDeclarationCollector implements Collector
{
    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     * @return mixed[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // return typed properties/all properties
        $classLike = $node->getOriginalNode();

        $propertyCount = count($classLike->getProperties());

        $missingTypeLines = [];

        foreach ($classLike->getProperties() as $property) {
            // blocked by parent type
            if ($this->isGuardedByParentClassProperty($scope, $property)) {
                continue;
            }

            // already typed
            if ($property->type instanceof Node) {
                continue;
            }

            if ($this->isPropertyDocTyped($property)) {
                continue;
            }

            // give useful context
            $missingTypeLines[] = $property->getLine();
        }

        return [$propertyCount, $missingTypeLines];
    }

    private function isPropertyDocTyped(Property $property): bool
    {
        $docComment = $property->getDocComment();
        if (! $docComment instanceof Doc) {
            return false;
        }

        $docCommentText = $docComment->getText();

        // skip as unable to type
        return strpos($docCommentText, 'callable') !== false || strpos($docCommentText, 'resource') !== false;
    }

    private function isGuardedByParentClassProperty(Scope $scope, Property $property): bool
    {
        $propertyName = $property->props[0]->name->toString();

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasProperty($propertyName)) {
                return true;
            }
        }

        return false;
    }
}
