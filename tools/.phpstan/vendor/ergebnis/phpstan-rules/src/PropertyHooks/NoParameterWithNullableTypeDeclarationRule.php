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

namespace Ergebnis\PHPStan\Rules\PropertyHooks;

use Ergebnis\PHPStan\Rules\Analyzer;
use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<ClassPropertyNode>
 */
final class NoParameterWithNullableTypeDeclarationRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Analyzer $analyzer;
    private Documentation\RuleDefinition $ruleDefinition;

    public function __construct(Analyzer $analyzer)
    {
        $this->analyzer = $analyzer;

        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noParameterWithNullableTypeDeclaration'),
            Documentation\Description::fromString('This rule reports an error when a [property hook](https://www.php.net/manual/en/language.oop5.property-hooks.php) has a parameter with a nullable type declaration.'),
            Documentation\Rationale::fromString('A parameter with a nullable type declaration makes the property hook handle two cases: a value and `null`. Separate properties or a null object make each case explicit.'),
            Documentation\Tip::fromString('Use a separate property or a null object instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    final class Greeting
                    {
                        public string $salutation = 'Hello' {
                            set(?string $value) {
                                $this->salutation = (string) $value;
                            }
                        }
                    }
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    namespace Example;

                    final class Greeting
                    {
                        public string $salutation = 'Hello' {
                            set(string $value) {
                                $this->salutation = $value;
                            }
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
        return ClassPropertyNode::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if (!$node->hasHooks()) {
            return [];
        }

        $propertyName = $node->getName();
        $classReflection = $node->getClassReflection();

        $errors = [];

        foreach ($node->getHooks() as $propertyHook) {
            $propertyHookName = $propertyHook->name->toString();

            foreach ($propertyHook->params as $parameter) {
                if (!$this->analyzer->isNullableTypeDeclaration($parameter->type)) {
                    continue;
                }

                /** @var Node\Expr\Variable $variable */
                $variable = $parameter->var;

                /** @var string $parameterName */
                $parameterName = $variable->name;

                if ($classReflection->isAnonymous()) {
                    $message = \sprintf(
                        'Property hook %s of property $%s in anonymous class has parameter $%s with a nullable type declaration.',
                        $propertyHookName,
                        $propertyName,
                        $parameterName,
                    );
                } else {
                    $message = \sprintf(
                        'Property hook %s of property %s::$%s has parameter $%s with a nullable type declaration.',
                        $propertyHookName,
                        $classReflection->getName(),
                        $propertyName,
                        $parameterName,
                    );
                }

                $errors[] = Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noParameterWithNullableTypeDeclaration()->toString())
                    ->tip(Documentation\ErrorTip::for($this))
                    ->line($parameter->getStartLine())
                    ->build();
            }
        }

        return $errors;
    }
}
