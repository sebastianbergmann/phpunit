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

use Ergebnis\PHPStan\Rules\Analyzer;
use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Reflection;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\ClassMethod>
 */
final class NoParameterWithNullDefaultValueRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Analyzer $analyzer;

    public function __construct(Analyzer $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'noParameterWithNullDefaultValue',
            'This rule reports an error when a method has a parameter with `null` as default value.',
            'A parameter with `null` as its default value makes the argument optional and nullable at once, so the method has to handle a missing value. Separate methods or a null object make each case explicit.',
            'Use separate methods or a null object instead.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example;

                final class Greeting
                {
                    public function greet($name = null): string
                    {
                        return \sprintf(
                            'Hello, %s!',
                            (string) $name,
                        );
                    }
                }
                PHP),
            Documentation\CodeSample::notReported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example;

                final class Greeting
                {
                    public function greet(string $name): string
                    {
                        return \sprintf(
                            'Hello, %s!',
                            $name,
                        );
                    }
                }
                PHP),
        );
    }

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

        $parametersWithNullDefaultValue = \array_values(\array_filter($node->params, function (Node\Param $parameter): bool {
            return $this->analyzer->hasNullDefaultValue($parameter);
        }));

        if (0 === \count($parametersWithNullDefaultValue)) {
            return [];
        }

        $methodName = $node->name->toString();

        /** @var Reflection\ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();

        if ($classReflection->isAnonymous()) {
            return \array_map(function (Node\Param $parameterWithNullDefaultValue) use ($methodName): Rules\RuleError {
                /** @var Node\Expr\Variable $variable */
                $variable = $parameterWithNullDefaultValue->var;

                /** @var string $parameterName */
                $parameterName = $variable->name;

                $message = \sprintf(
                    'Method %s() in anonymous class has parameter $%s with null as default value.',
                    $methodName,
                    $parameterName,
                );

                return Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noParameterWithNullDefaultValue()->toString())
                    ->tip(Documentation\ErrorTip::for($this))
                    ->build();
            }, $parametersWithNullDefaultValue);
        }

        $className = $classReflection->getName();

        return \array_map(function (Node\Param $parameterWithNullDefaultValue) use ($className, $methodName): Rules\RuleError {
            /** @var Node\Expr\Variable $variable */
            $variable = $parameterWithNullDefaultValue->var;

            /** @var string $parameterName */
            $parameterName = $variable->name;

            $message = \sprintf(
                'Method %s::%s() has parameter $%s with null as default value.',
                $className,
                $methodName,
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterWithNullDefaultValue()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build();
        }, $parametersWithNullDefaultValue);
    }
}
