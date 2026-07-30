<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use Rector\TypePerfect\Configuration;
use Rector\TypePerfect\NodeFinder\ClassMethodNodeFinder;
use Rector\TypePerfect\NodeFinder\MethodCallNodeFinder;

/**
 * @see \Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule\NarrowPrivateClassMethodParamTypeRuleTest
 * @implements Rule<MethodCall>
 */
final readonly class NarrowPrivateClassMethodParamTypeRule implements Rule
{
    public const string ERROR_MESSAGE = 'Parameter %d should use "%s" type as the only type passed to this method';

    public function __construct(
        private Configuration $configuration,
        private MethodCallNodeFinder $methodCallNodeFinder,
        private ClassMethodNodeFinder $classMethodNodeFinder
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->configuration->isNarrowParamEnabled() || $node->isFirstClassCallable()) {
            return [];
        }

        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        if (! $node->var instanceof Variable) {
            return [];
        }

        if (! is_string($node->var->name)) {
            return [];
        }

        if ($node->var->name !== 'this') {
            return [];
        }

        return $this->validateArgVsParamTypes($args, $node, $scope);
    }

    /**
     * @param Arg[] $args
     * @return RuleError[]
     */
    private function validateArgVsParamTypes(array $args, MethodCall $methodCall, Scope $scope): array
    {
        $methodCallUses = $this->methodCallNodeFinder->findUsages($methodCall, $scope);
        if (count($methodCallUses) > 1) {
            return [];
        }

        $classMethod = $this->classMethodNodeFinder->findByMethodCall($methodCall, $scope);
        if (! $classMethod instanceof ClassMethod) {
            return [];
        }

        /** @var Param[] $params */
        $params = $classMethod->getParams();

        $errorMessages = [];

        foreach ($args as $position => $arg) {
            $param = $params[$position] ?? [];
            if (! $param instanceof Param) {
                continue;
            }

            if ($param->variadic) {
                return [];
            }

            $paramRuleError = $this->validateParam($param, $position, $arg->value, $scope);
            if (! $paramRuleError instanceof RuleError) {
                continue;
            }

            // @todo test double failed type
            $errorMessages[] = $paramRuleError;
        }

        return $errorMessages;
    }

    private function validateParam(Param $param, int $position, Expr $expr, Scope $scope): ?RuleError
    {
        $type = $param->type;

        // @todo some static type mapper from php-parser to PHPStan?
        if (! $type instanceof FullyQualified) {
            return null;
        }

        $argType = $scope->getType($expr);
        if ($argType instanceof MixedType) {
            return null;
        }

        if ($argType instanceof TemplateType) {
            return null;
        }

        // not solveable yet, work with PHP 8 code only
        if ($argType instanceof UnionType) {
            return null;
        }

        if ($argType instanceof IntersectionType || $argType instanceof GenericObjectType) {
            return null;
        }

        $objectType = new ObjectType($type->toString());

        if ($objectType->equals($argType)) {
            return null;
        }

        $classReflection = $objectType->getClassReflection();
        if ($classReflection instanceof ClassReflection && $classReflection->isAbstract()) {
            return null;
        }

        // handle weird type substration cases
        $paramTypeAsString = $objectType->describe(VerbosityLevel::typeOnly());
        $argTypeAsString = $argType->describe(VerbosityLevel::typeOnly());

        if ($paramTypeAsString === $argTypeAsString) {
            return null;
        }

        $errorMessage = sprintf(self::ERROR_MESSAGE, $position + 1, $argTypeAsString);

        return RuleErrorBuilder::message($errorMessage)
            ->identifier('typePerfect.narrowPrivateClassMethodParamType')
            ->line($param->getLine())
            ->build();
    }
}
