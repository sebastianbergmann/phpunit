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

namespace Ergebnis\PHPStan\Rules\CallLikes;

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Expr\CallLike>
 */
final class NoNamedArgumentRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'noNamedArgument',
            'This rule reports an error when an anonymous function, a function, or a method is invoked using a [named argument](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments).',
            'Named arguments make the names of parameters part of the public API, so renaming a parameter breaks callers. Positional arguments keep the names of parameters an implementation detail.',
            'Use positional arguments instead.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                function greet(string $name): string
                {
                    return \sprintf(
                        'Hello, %s!',
                        $name,
                    );
                }

                greet(name: 'Jane');
                PHP),
            Documentation\CodeSample::notReported(<<<'PHP'
                <?php

                declare(strict_types=1);

                function greet(string $name): string
                {
                    return \sprintf(
                        'Hello, %s!',
                        $name,
                    );
                }

                greet('Jane');
                PHP),
        );
    }

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

        $callLike = self::describeCallLike(
            $node,
            $scope,
        );

        return \array_map(function (Node\Arg $namedArgument) use ($callLike): Rules\RuleError {
            /** @var Node\Identifier $argumentName */
            $argumentName = $namedArgument->name;

            $message = \sprintf(
                '%s is invoked with named argument for parameter $%s.',
                $callLike,
                $argumentName->toString(),
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noNamedArgument()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build();
        }, $namedArguments);
    }

    private static function describeCallLike(
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
                    $functionName->name,
                );
            }
        }

        if ($node instanceof Node\Expr\MethodCall) {
            $methodName = $node->name;

            if ($methodName instanceof Node\Identifier) {
                $objectType = $scope->getType($node->var);

                $methodReflection = $scope->getMethodReflection(
                    $objectType,
                    $methodName->name,
                );

                if (null === $methodReflection) {
                    $objectClassNames = $objectType->getObjectClassNames();

                    if (1 === \count($objectClassNames)) {
                        return \sprintf(
                            'Method %s::%s()',
                            $objectClassNames[0],
                            $methodName->toString(),
                        );
                    }

                    return \sprintf(
                        'Method %s()',
                        $methodName->toString(),
                    );
                }

                $declaringClass = $methodReflection->getDeclaringClass();

                if ($declaringClass->isAnonymous()) {
                    return \sprintf(
                        'Method %s() of anonymous class',
                        $methodName->toString(),
                    );
                }

                return \sprintf(
                    'Method %s::%s()',
                    $declaringClass->getName(),
                    $methodName->toString(),
                );
            }

            return 'Method';
        }

        if ($node instanceof Node\Expr\StaticCall) {
            $methodName = $node->name;

            if ($methodName instanceof Node\Identifier) {
                $className = $node->class;

                if ($className instanceof Node\Name) {
                    return \sprintf(
                        'Method %s::%s()',
                        $className->toString(),
                        $methodName->toString(),
                    );
                }

                return \sprintf(
                    'Method %s()',
                    $methodName->toString(),
                );
            }

            return 'Method';
        }

        if ($node instanceof Node\Expr\New_) {
            $className = $node->class;

            if ($className instanceof Node\Name) {
                return \sprintf(
                    'Constructor of %s',
                    $className->toString(),
                );
            }

            return 'Constructor';
        }

        return 'Callable';
    }
}
