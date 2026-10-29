<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Methods;

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Reflection;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\ClassMethod>
 */
final class NoParameterWithContainerTypeDeclarationRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
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
    private Documentation\RuleDefinition $ruleDefinition;

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

        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noParameterWithContainerTypeDeclaration'),
            Documentation\Description::fromString('This rule reports an error when a method has a type declaration for a known dependency injection container or service locator.'),
            Documentation\Rationale::fromString('A method that receives a dependency injection container or service locator hides its actual dependencies. Inject the dependencies directly instead.'),
            Documentation\Tip::fromString('Inject the dependencies directly instead.'),
            Documentation\ParameterList::create(
                Documentation\Parameter::create(
                    Documentation\ParameterName::fromString('interfacesImplementedByContainers'),
                    Documentation\Description::fromString(<<<'MARKDOWN'
                    By default, this rule disallows the use of type declarations indicating an implementation of

                    - [`Psr\Container\ContainerInterface`](https://github.com/php-fig/container/blob/1.0.0/src/ContainerInterface.php)

                    is expected to be injected into a method.

                    You can set the `interfacesImplementedByContainers` parameter to a list of interface names of additional containers and service locators.
                    MARKDOWN),
                    Documentation\ParameterExample::fromStrings(
                        'Fancy\\DependencyInjection\\ContainerInterface',
                        'Other\\ServiceLocatorInterface',
                    ),
                ),
                Documentation\Parameter::create(
                    Documentation\ParameterName::fromString('methodsAllowedToUseContainerTypeDeclarations'),
                    Documentation\Description::fromString('You can set the `methodsAllowedToUseContainerTypeDeclarations` parameter to a list of method names that are allowed to use parameters with container type declarations.'),
                    Documentation\ParameterExample::fromStrings('loadExtension'),
                ),
            ),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    use Psr\Container;

                    final class Greeting
                    {
                        public function greet(Container\ContainerInterface $container): string
                        {
                            return $container->get('salutation');
                        }
                    }
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    final class Salutation
                    {
                        public function toString(): string
                        {
                            return 'Hello';
                        }
                    }

                    final class Greeting
                    {
                        public function greet(Salutation $salutation): string
                        {
                            return $salutation->toString();
                        }
                    }
                    PHP),
            ),
        );
    }

    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return $this->ruleDefinition;
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
                            $errors[] = $this->createError(
                                $containingClass,
                                $methodName,
                                $parameterName,
                                $classUsedInTypeDeclaration,
                            );

                            return $errors;
                        }

                        if ($classUsedInTypeDeclaration->getNativeReflection()->isSubclassOf($interfaceImplementedByContainer)) {
                            $errors[] = $this->createError(
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
                        $errors[] = $this->createError(
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

    private function createError(
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
                ->tip(Documentation\ErrorTip::for($this))
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
            ->tip(Documentation\ErrorTip::for($this))
            ->build();
    }
}
