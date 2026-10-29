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
final class NoNullableReturnTypeDeclarationRule implements
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
            'noNullableReturnTypeDeclaration',
            'This rule reports an error when a method uses a nullable return type declaration.',
            'A nullable return type forces every caller to handle `null`. Throwing an exception, returning a null object, or splitting the method in two keeps the callers simpler.',
            'Throw an exception, return a null object, or split the method in two instead.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example;

                final class Greeting
                {
                    public function greet(string $name): ?string
                    {
                        return \sprintf(
                            'Hello, %s!',
                            $name,
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
        if (!$this->analyzer->isNullableTypeDeclaration($node->getReturnType())) {
            return [];
        }

        /** @var Reflection\ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();

        if ($classReflection->isAnonymous()) {
            $message = \sprintf(
                'Method %s() in anonymous class has a nullable return type declaration.',
                $node->name->name,
            );

            return [
                Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noNullableReturnTypeDeclaration()->toString())
                    ->tip(Documentation\ErrorTip::for($this))
                    ->build(),
            ];
        }

        $message = \sprintf(
            'Method %s::%s() has a nullable return type declaration.',
            $classReflection->getName(),
            $node->name->name,
        );

        return [
            Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noNullableReturnTypeDeclaration()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
