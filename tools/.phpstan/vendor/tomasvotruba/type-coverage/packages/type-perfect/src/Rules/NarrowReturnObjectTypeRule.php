<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\TypePerfect\Configuration;
use Rector\TypePerfect\NodeFinder\ReturnNodeFinder;
use Rector\TypePerfect\Reflection\MethodNodeAnalyser;

/**
 * @see \Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\NarrowReturnObjectTypeRuleTest
 *
 * @implements Rule<ClassMethod>
 */
final readonly class NarrowReturnObjectTypeRule implements Rule
{
    public const string ERROR_MESSAGE = 'Provide more specific return type "%s" over abstract one';

    public function __construct(
        private ReturnNodeFinder $returnNodeFinder,
        private MethodNodeAnalyser $methodNodeAnalyser,
        private Configuration $configuration
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->configuration->isNarrowReturnEnabled()) {
            return [];
        }

        if (! $node->returnType instanceof FullyQualified) {
            return [];
        }

        if ($this->shouldSkipScope($scope)) {
            return [];
        }

        $returnObjectType = new ObjectType($node->returnType->toString());
        if ($this->shouldSkipReturnObjectType($returnObjectType)) {
            return [];
        }

        $methodName = $node->name->toString();
        if ($this->methodNodeAnalyser->hasParentVendorLock($scope, $methodName)) {
            return [];
        }

        $returnExpr = $this->returnNodeFinder->findOnlyReturnsExpr($node);
        if (! $returnExpr instanceof Expr) {
            return [];
        }

        $returnExprType = $scope->getType($returnExpr);
        if ($this->shouldSkipReturnExprType($returnExprType)) {
            return [];
        }

        if ($returnObjectType->equals($returnExprType)) {
            return [];
        }

        // is subtype?
        if (! $returnObjectType->isSuperTypeOf($returnExprType)->yes()) {
            return [];
        }

        if (count($returnExprType->getObjectClassNames()) !== 1) {
            return [];
        }

        $errorMessage = sprintf(self::ERROR_MESSAGE, $returnExprType->getObjectClassNames()[0]);

        return [
            RuleErrorBuilder::message($errorMessage)
                ->identifier('typePerfect.narrowReturnObjectType')
                ->build(),
        ];
    }

    private function shouldSkipReturnObjectType(ObjectType $objectType): bool
    {
        $classReflection = $objectType->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        // cannot be more precise if final class
        return $classReflection->isFinal();
    }

    private function shouldSkipScope(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        return ! $classReflection->isClass();
    }

    private function shouldSkipReturnExprType(Type $type): bool
    {
        if (count($type->getObjectClassNames()) !== 1) {
            return true;
        }

        if (count($type->getObjectClassReflections()) !== 1) {
            return true;
        }

        return $type->getObjectClassReflections()[0]
            ->isAnonymous();
    }
}
