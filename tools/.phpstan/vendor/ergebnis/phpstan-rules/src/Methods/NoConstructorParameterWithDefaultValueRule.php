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
final class NoConstructorParameterWithDefaultValueRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'noConstructorParameterWithDefaultValue',
            'This rule reports an error when a constructor has a parameter with a default value.',
            'A constructor parameter with a default value lets callers create an object without deciding on that value. Named constructors make each way of creating the object explicit.',
            'Remove the default value and add a named constructor for each way of creating the object.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example;

                final class Greeting
                {
                    private string $salutation;

                    public function __construct(string $salutation = 'Hello')
                    {
                        $this->salutation = $salutation;
                    }
                }
                PHP),
            Documentation\CodeSample::notReported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example;

                final class Greeting
                {
                    private string $salutation;

                    public function __construct(string $salutation)
                    {
                        $this->salutation = $salutation;
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
            return \array_map(function (Node\Param $parameterWithDefaultValue): Rules\RuleError {
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
                    ->tip(Documentation\ErrorTip::for($this))
                    ->build();
            }, $parametersWithDefaultValue);
        }

        $className = $classReflection->getName();

        return \array_map(function (Node\Param $parameterWithDefaultValue) use ($className): Rules\RuleError {
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
                ->tip(Documentation\ErrorTip::for($this))
                ->build();
        }, $parametersWithDefaultValue);
    }

    private static function hasDefaultValue(Node\Param $parameter): bool
    {
        return null !== $parameter->default;
    }
}
