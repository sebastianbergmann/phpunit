<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\TypePerfect\Configuration;
use Rector\TypePerfect\Reflection\MethodNodeAnalyser;

/**
 * @see \Rector\TypePerfect\Tests\Rules\ForbiddenParamTypeRemovalRule\ForbiddenParamTypeRemovalRuleTest
 * @implements Rule<ClassMethod>
 */
final readonly class NoParamTypeRemovalRule implements Rule
{
    public const string ERROR_MESSAGE = 'Removing parent param type is forbidden';

    public function __construct(
        private MethodNodeAnalyser $methodNodeAnalyser,
        private Configuration $configuration,
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
        if (! $this->configuration->isNoParamTypeRemovalEnabled()) {
            return [];
        }

        if ($node->params === []) {
            return [];
        }

        $classMethodName = (string) $node->name;
        if ($classMethodName === '__construct') {
            return [];
        }

        $parentClassMethodReflection = $this->methodNodeAnalyser->matchFirstParentClassMethod($scope, $classMethodName);
        if (! $parentClassMethodReflection instanceof PhpMethodReflection) {
            return [];
        }

        foreach ($node->params as $paramPosition => $param) {
            if ($param->type !== null) {
                continue;
            }

            $parentParamType = $this->resolveParentParamType($parentClassMethodReflection, $paramPosition);
            if ($parentParamType instanceof MixedType) {
                continue;
            }

            // removed param type!
            return [
                RuleErrorBuilder::message(self::ERROR_MESSAGE)
                    ->identifier('typePerfect.noParamTypeRemoval')
                    ->build(),
            ];
        }

        return [];
    }

    private function resolveParentParamType(PhpMethodReflection $phpMethodReflection, int $paramPosition): Type
    {
        foreach ($phpMethodReflection->getVariants() as $extendedParametersAcceptor) {
            foreach ($extendedParametersAcceptor->getParameters() as $parentParamPosition => $parameterReflectionWithPhpDoc) {
                if ($paramPosition !== $parentParamPosition) {
                    continue;
                }

                return $parameterReflectionWithPhpDoc->getNativeType();
            }
        }

        return new MixedType();
    }
}
