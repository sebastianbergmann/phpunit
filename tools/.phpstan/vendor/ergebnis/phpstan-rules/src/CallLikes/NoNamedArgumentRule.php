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

        if ($node instanceof Node\Expr\FuncCall) {
            $functionName = $node->name;

            if ($functionName instanceof Node\Expr\Variable) {
                return \array_map(static function (Node\Arg $namedArgument) use ($functionName): Rules\RuleError {
                    /** @var Node\Identifier $argumentName */
                    $argumentName = $namedArgument->name;

                    $message = \sprintf(
                        'Anonymous function referenced by $%s is invoked with named argument for parameter $%s.',
                        $functionName->name,
                        $argumentName->toString(),
                    );

                    return Rules\RuleErrorBuilder::message($message)
                        ->identifier(ErrorIdentifier::noNamedArgument()->toString())
                        ->build();
                }, $namedArguments);
            }

            return \array_map(static function (Node\Arg $namedArgument) use ($functionName): Rules\RuleError {
                /** @var Node\Identifier $argumentName */
                $argumentName = $namedArgument->name;

                $message = \sprintf(
                    'Function %s() is invoked with named argument for parameter $%s.',
                    $functionName,
                    $argumentName->toString(),
                );

                return Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noNamedArgument()->toString())
                    ->build();
            }, $namedArguments);
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
                return \array_map(static function (Node\Arg $namedArgument) use ($methodName): Rules\RuleError {
                    /** @var Node\Identifier $argumentName */
                    $argumentName = $namedArgument->name;

                    $message = \sprintf(
                        'Method %s() of anonymous class is invoked with named argument for parameter $%s.',
                        $methodName,
                        $argumentName->toString(),
                    );

                    return Rules\RuleErrorBuilder::message($message)
                        ->identifier(ErrorIdentifier::noNamedArgument()->toString())
                        ->build();
                }, $namedArguments);
            }

            return \array_map(static function (Node\Arg $namedArgument) use ($declaringClass, $methodName): Rules\RuleError {
                /** @var Node\Identifier $argumentName */
                $argumentName = $namedArgument->name;

                $message = \sprintf(
                    'Method %s::%s() is invoked with named argument for parameter $%s.',
                    $declaringClass->getName(),
                    $methodName,
                    $argumentName->toString(),
                );

                return Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noNamedArgument()->toString())
                    ->build();
            }, $namedArguments);
        }

        if ($node instanceof Node\Expr\StaticCall) {
            $className = $node->class;
            $methodName = $node->name;

            return \array_map(static function (Node\Arg $namedArgument) use ($className, $methodName): Rules\RuleError {
                /** @var Node\Identifier $argumentName */
                $argumentName = $namedArgument->name;

                $message = \sprintf(
                    'Method %s::%s() is invoked with named argument for parameter $%s.',
                    $className,
                    $methodName,
                    $argumentName->toString(),
                );

                return Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noNamedArgument()->toString())
                    ->build();
            }, $namedArguments);
        }

        if ($node instanceof Node\Expr\New_) {
            /** @var Node\Name\FullyQualified $className */
            $className = $node->class;

            return \array_map(static function (Node\Arg $namedArgument) use ($className): Rules\RuleError {
                /** @var Node\Identifier $argumentName */
                $argumentName = $namedArgument->name;

                $message = \sprintf(
                    'Constructor of %s is invoked with named argument for parameter $%s.',
                    $className->toString(),
                    $argumentName->toString(),
                );

                return Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noNamedArgument()->toString())
                    ->build();
            }, $namedArguments);
        }

        return [];
    }
}
