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

namespace Ergebnis\PHPStan\Rules\CallLikes;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rules\Rule<Node\Expr\CallLike>
 */
final class NoNamedArgumentRule implements Rules\Rule
{
    public function getNodeType(): string
    {
        return Node\Expr\CallLike::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if (0 === \count($node->getArgs())) {
            return [];
        }

        /** @var list<Node\Arg> $namedArguments */
        $namedArguments = \array_values(\array_filter($node->getArgs(), static function (Node\Arg $argument): bool {
            if (!$argument->name instanceof Node\Identifier) {
                return false;
            }

            return true;
        }));

        if (0 === \count($namedArguments)) {
            return [];
        }

        $callable = self::describeCallable(
            $node,
            $scope,
        );

        return \array_map(static function (Node\Arg $namedArgument) use ($callable): Rules\RuleError {
            /** @var Node\Identifier $argumentName */
            $argumentName = $namedArgument->name;

            $message = \sprintf(
                '%s is invoked with named argument for parameter $%s.',
                $callable,
                $argumentName->toString(),
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noNamedArgument()->toString())
                ->build();
        }, $namedArguments);
    }

    private static function describeCallable(
        Node\Expr\CallLike $node,
        Analyser\Scope $scope
    ): string {
        if ($node instanceof Node\Expr\FuncCall) {
            $functionName = $node->name;

            if ($functionName instanceof Node\Expr\PropertyFetch) {
                return \sprintf(
                    'Callable referenced by property $%s',
                    $functionName->name,
                );
            }

            if ($functionName instanceof Node\Expr\Variable) {
                return \sprintf(
                    'Callable referenced by $%s',
                    $functionName->name,
                );
            }

            if ($functionName instanceof Node\Name) {
                return \sprintf(
                    'Function %s()',
                    $functionName,
                );
            }
        }

        if ($node instanceof Node\Expr\MethodCall) {
            /** @var Node\Identifier $methodName */
            $methodName = $node->name;

            $objectType = $scope->getType($node->var);

            $methodReflection = $scope->getMethodReflection(
                $objectType,
                $methodName->name,
            );

            if (null === $methodReflection) {
                throw new ShouldNotHappenException();
            }

            $declaringClass = $methodReflection->getDeclaringClass();

            if ($declaringClass->isAnonymous()) {
                return \sprintf(
                    'Method %s() of anonymous class',
                    $methodName,
                );
            }

            return \sprintf(
                'Method %s::%s()',
                $declaringClass->getName(),
                $methodName,
            );
        }

        if ($node instanceof Node\Expr\StaticCall) {
            $className = $node->class;

            /** @var Node\Identifier $methodName */
            $methodName = $node->name;

            if ($className instanceof Node\Expr\Variable) {
                return \sprintf(
                    'Method %s()',
                    $methodName,
                );
            }

            return \sprintf(
                'Method %s::%s()',
                $className,
                $methodName,
            );
        }

        if ($node instanceof Node\Expr\New_) {
            /** @var Node\Name\FullyQualified $className */
            $className = $node->class;

            return \sprintf(
                'Constructor of %s',
                $className->toString(),
            );
        }

        return 'Callable';
    }
}
