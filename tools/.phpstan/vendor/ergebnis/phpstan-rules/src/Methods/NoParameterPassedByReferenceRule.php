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
final class NoParameterPassedByReferenceRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Documentation\RuleDefinition $ruleDefinition;

    public function __construct()
    {
        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noParameterPassedByReference'),
            Documentation\Description::fromString('This rule reports an error when a method has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).'),
            Documentation\Rationale::fromString('A parameter passed by reference lets a method change a variable of its caller as a side effect, which makes the code harder to follow. Return the new value instead.'),
            Documentation\Tip::fromString('Return the new value instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    final class Greeting
                    {
                        public function increment(int &$value): void
                        {
                            ++$value;
                        }
                    }
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    final class Greeting
                    {
                        public function increment(int $value): int
                        {
                            return $value + 1;
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
            return \array_map(function (Node\Param $parameterExplicitlyPassedByReference) use ($methodName): Rules\RuleError {
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
                    ->tip(Documentation\ErrorTip::for($this))
                    ->build();
            }, $parametersExplicitlyPassedByReference);
        }

        $className = $classReflection->getName();

        return \array_map(function (Node\Param $parameterExplicitlyPassedByReference) use ($className, $methodName): Rules\RuleError {
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
                ->tip(Documentation\ErrorTip::for($this))
                ->build();
        }, $parametersExplicitlyPassedByReference);
    }
}
