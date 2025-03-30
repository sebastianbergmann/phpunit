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
final class NoConstructorParameterWithDefaultValueRule implements Rules\Rule
{
    public function getNodeType(): string
    {
        return Node\Stmt\ClassMethod::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if ('__construct' !== $node->name->toLowerString()) {
            return [];
        }

        if (0 === \count($node->params)) {
            return [];
        }

        $parametersWithDefaultValue = \array_values(\array_filter($node->params, static function (Node\Param $parameter): bool {
            return self::hasDefaultValue($parameter);
        }));

        if (0 === \count($parametersWithDefaultValue)) {
            return [];
        }

        /** @var Reflection\ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();

        if ($classReflection->isAnonymous()) {
            return \array_map(static function (Node\Param $parameterWithDefaultValue): Rules\RuleError {
                /** @var Node\Expr\Variable $variable */
                $variable = $parameterWithDefaultValue->var;

                /** @var string $parameterName */
                $parameterName = $variable->name;

                $message = \sprintf(
                    'Constructor in anonymous class has parameter $%s with default value.',
                    $parameterName,
                );

                return Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noConstructorParameterWithDefaultValue()->toString())
                    ->build();
            }, $parametersWithDefaultValue);
        }

        $className = $classReflection->getName();

        return \array_map(static function (Node\Param $parameterWithDefaultValue) use ($className): Rules\RuleError {
            /** @var Node\Expr\Variable $variable */
            $variable = $parameterWithDefaultValue->var;

            /** @var string $parameterName */
            $parameterName = $variable->name;

            $message = \sprintf(
                'Constructor in %s has parameter $%s with default value.',
                $className,
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noConstructorParameterWithDefaultValue()->toString())
                ->build();
        }, $parametersWithDefaultValue);
    }

    private static function hasDefaultValue(Node\Param $parameter): bool
    {
        return null !== $parameter->default;
    }
}
