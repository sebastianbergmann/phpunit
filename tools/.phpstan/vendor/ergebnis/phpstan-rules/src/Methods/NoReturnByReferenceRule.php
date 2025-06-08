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

namespace Ergebnis\PHPStan\Rules\Methods;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Reflection;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\ClassMethod>
 */
final class NoReturnByReferenceRule implements Rules\Rule
{
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
                ->build(),
        ];
    }
}
