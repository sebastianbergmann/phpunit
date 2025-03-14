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
final class NoParameterPassedByReferenceRule implements Rules\Rule
{
    public function getNodeType(): string
    {
        return Node\Stmt\ClassMethod::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if (0 === \count($node->params)) {
            return [];
        }

        $parametersExplicitlyPassedByReference = \array_values(\array_filter($node->params, static function (Node\Param $parameter): bool {
            return $parameter->byRef;
        }));

        if (0 === \count($parametersExplicitlyPassedByReference)) {
            return [];
        }

        $methodName = $node->name->toString();

        /** @var Reflection\ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();

        if ($classReflection->isAnonymous()) {
            return \array_map(static function (Node\Param $parameterExplicitlyPassedByReference) use ($methodName): Rules\RuleError {
                /** @var Node\Expr\Variable $variable */
                $variable = $parameterExplicitlyPassedByReference->var;

                /** @var string $parameterName */
                $parameterName = $variable->name;

                $message = \sprintf(
                    'Method %s() in anonymous class has parameter $%s that is passed by reference.',
                    $methodName,
                    $parameterName,
                );

                return Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noParameterPassedByReference()->toString())
                    ->build();
            }, $parametersExplicitlyPassedByReference);
        }

        $className = $classReflection->getName();

        return \array_map(static function (Node\Param $parameterExplicitlyPassedByReference) use ($className, $methodName): Rules\RuleError {
            /** @var Node\Expr\Variable $variable */
            $variable = $parameterExplicitlyPassedByReference->var;

            /** @var string $parameterName */
            $parameterName = $variable->name;

            $message = \sprintf(
                'Method %s::%s() has parameter $%s that is passed by reference.',
                $className,
                $methodName,
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterPassedByReference()->toString())
                ->build();
        }, $parametersExplicitlyPassedByReference);
    }
}
