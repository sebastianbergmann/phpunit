<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2025 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Methods;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Reflection;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\ClassMethod>
 */
final class NoParameterWithContainerTypeDeclarationRule implements Rules\Rule
{
    private Reflection\ReflectionProvider $reflectionProvider;

    /**
     * @var list<string>
     */
    private array $interfacesImplementedByContainers;

    /**
     * @var list<string>
     */
    private array $methodsAllowedToUseContainerTypeDeclarations;

    /**
     * @param list<string> $interfacesImplementedByContainers
     * @param list<string> $methodsAllowedToUseContainerTypeDeclarations
     */
    public function __construct(
        Reflection\ReflectionProvider $reflectionProvider,
        array $interfacesImplementedByContainers,
        array $methodsAllowedToUseContainerTypeDeclarations
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->interfacesImplementedByContainers = \array_values(\array_filter(
            \array_map(static function (string $interfaceImplementedByContainers): string {
                return $interfaceImplementedByContainers;
            }, $interfacesImplementedByContainers),
            static function (string $interfaceImplementedByContainer): bool {
                return \interface_exists($interfaceImplementedByContainer);
            },
        ));
        $this->methodsAllowedToUseContainerTypeDeclarations = $methodsAllowedToUseContainerTypeDeclarations;
    }

    public function getNodeType(): string
    {
        return Node\Stmt\ClassMethod::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if (0 === \count($this->interfacesImplementedByContainers)) {
            return [];
        }

        if (0 === \count($node->params)) {
            return [];
        }

        $methodName = $node->name->toString();

        if (\in_array($methodName, $this->methodsAllowedToUseContainerTypeDeclarations, true)) {
            return [];
        }

        /** @var Reflection\ClassReflection $containingClass */
        $containingClass = $scope->getClassReflection();

        return \array_values(\array_reduce(
            $node->params,
            function (array $errors, Node\Param $node) use ($scope, $containingClass, $methodName): array {
                $type = $node->type;

                if (!$type instanceof Node\Name) {
                    return $errors;
                }

                /** @var Node\Expr\Variable $variable */
                $variable = $node->var;

                /** @var string $parameterName */
                $parameterName = $variable->name;

                $classUsedInTypeDeclaration = $this->reflectionProvider->getClass($scope->resolveName($type));

                if ($classUsedInTypeDeclaration->isInterface()) {
                    foreach ($this->interfacesImplementedByContainers as $interfaceImplementedByContainer) {
                        if ($classUsedInTypeDeclaration->getName() === $interfaceImplementedByContainer) {
                            $errors[] = self::createError(
                                $containingClass,
                                $methodName,
                                $parameterName,
                                $classUsedInTypeDeclaration,
                            );

                            return $errors;
                        }

                        if ($classUsedInTypeDeclaration->getNativeReflection()->isSubclassOf($interfaceImplementedByContainer)) {
                            $errors[] = self::createError(
                                $containingClass,
                                $methodName,
                                $parameterName,
                                $classUsedInTypeDeclaration,
                            );

                            return $errors;
                        }
                    }
                }

                foreach ($this->interfacesImplementedByContainers as $interfaceImplementedByContainer) {
                    if ($classUsedInTypeDeclaration->getNativeReflection()->implementsInterface($interfaceImplementedByContainer)) {
                        $errors[] = self::createError(
                            $containingClass,
                            $methodName,
                            $parameterName,
                            $classUsedInTypeDeclaration,
                        );

                        return $errors;
                    }
                }

                return $errors;
            },
            [],
        ));
    }

    private static function createError(
        Reflection\ClassReflection $classReflection,
        string $methodName,
        string $parameterName,
        Reflection\ClassReflection $classUsedInTypeDeclaration
    ): Rules\RuleError {
        if ($classReflection->isAnonymous()) {
            $message = \sprintf(
                'Method %s() in anonymous class has a parameter $%s with a type declaration of %s, but containers should not be injected.',
                $methodName,
                $parameterName,
                $classUsedInTypeDeclaration->getName(),
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterWithContainerTypeDeclaration()->toString())
                ->build();
        }

        $message = \sprintf(
            'Method %s::%s() has a parameter $%s with a type declaration of %s, but containers should not be injected.',
            $classReflection->getName(),
            $methodName,
            $parameterName,
            $classUsedInTypeDeclaration->getName(),
        );

        return Rules\RuleErrorBuilder::message($message)
            ->identifier(ErrorIdentifier::noParameterWithContainerTypeDeclaration()->toString())
            ->build();
    }
}
