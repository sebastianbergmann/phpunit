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
final class NoReturnByReferenceRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'noReturnByReference',
            'This rule reports an error when a method [returns by reference](https://www.php.net/manual/en/language.references.return.php).',
            'Returning by reference lets the caller change state that the method keeps, which makes the code harder to follow. Return a value instead.',
            'Return a value instead.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                namespace Example;

                final class Greetings
                {
                    private array $values = [];

                    public function &values(): array
                    {
                        return $this->values;
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

                    public function values(): array
                    {
                        return $this->values;
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
        if (false === $node->byRef) {
            return [];
        }

        $methodName = $node->name->toString();

        /** @var Reflection\ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();

        if ($classReflection->isAnonymous()) {
            $message = \sprintf(
                'Method %s() in anonymous class returns by reference.',
                $methodName,
            );

            return [
                Rules\RuleErrorBuilder::message($message)
                    ->identifier(ErrorIdentifier::noReturnByReference()->toString())
                    ->tip(Documentation\ErrorTip::for($this))
                    ->build(),
            ];
        }

        $className = $classReflection->getName();

        $message = \sprintf(
            'Method %s::%s() returns by reference.',
            $className,
            $methodName,
        );

        return [
            Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noReturnByReference()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
