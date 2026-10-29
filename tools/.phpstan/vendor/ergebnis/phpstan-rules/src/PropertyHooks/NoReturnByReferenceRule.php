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

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<ClassPropertyNode>
 */
final class NoReturnByReferenceRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'noReturnByReference',
            'This rule reports an error when a [property hook](https://www.php.net/manual/en/language.oop5.property-hooks.php) returns by reference.',
            'Returning by reference lets the caller change state that the property hook keeps, which makes the code harder to follow. Return a value instead.',
            'Return a value instead.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example;

                final class Greetings
                {
                    private array $values = [];

                    public array $salutations {
                        &get => $this->values;
                    }
                }
                PHP),
            Documentation\CodeSample::notReported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example;

                final class Greetings
                {
                    private array $values = [];

                    public array $salutations {
                        get => $this->values;
                    }
                }
                PHP),
        );
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

        $propertyHooksReturningByReference = \array_values(\array_filter($node->getHooks(), static function (Node\PropertyHook $propertyHook): bool {
            return $propertyHook->byRef;
        }));

        if (0 === \count($propertyHooksReturningByReference)) {
            return [];
        }

        $propertyName = $node->getName();
        $classReflection = $node->getClassReflection();

        if ($classReflection->isAnonymous()) {
            return \array_map(function (Node\PropertyHook $propertyHookReturningByReference) use ($propertyName): Rules\RuleError {
                $message = \sprintf(
                    'Property hook %s of property $%s in anonymous class returns by reference.',
                    $propertyHookReturningByReference->name->toString(),
                    $propertyName,
                );

                return Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noReturnByReference()->toString())
                    ->tip(Documentation\ErrorTip::for($this))
                    ->line($propertyHookReturningByReference->getStartLine())
                    ->build();
            }, $propertyHooksReturningByReference);
        }

        $className = $classReflection->getName();

        return \array_map(function (Node\PropertyHook $propertyHookReturningByReference) use ($className, $propertyName): Rules\RuleError {
            $message = \sprintf(
                'Property hook %s of property %s::$%s returns by reference.',
                $propertyHookReturningByReference->name->toString(),
                $className,
                $propertyName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noReturnByReference()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->line($propertyHookReturningByReference->getStartLine())
                ->build();
        }, $propertyHooksReturningByReference);
    }
}
