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

namespace Ergebnis\PHPStan\Rules\Functions;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Function_>
 */
final class NoParameterPassedByReferenceRule implements Rules\Rule
{
    public function getNodeType(): string
    {
        return Node\Stmt\Function_::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if (0 === \count($node->params)) {
            return [];
        }

        $parametersPassedByReference = \array_values(\array_filter($node->params, static function (Node\Param $parameter): bool {
            return $parameter->byRef;
        }));

        if (0 === \count($parametersPassedByReference)) {
            return [];
        }

        $functionName = $node->namespacedName;

        return \array_map(static function (Node\Param $parameterPassedByReference) use ($functionName): Rules\RuleError {
            /** @var Node\Expr\Variable $variable */
            $variable = $parameterPassedByReference->var;

            /** @var string $parameterName */
            $parameterName = $variable->name;

            $message = \sprintf(
                'Function %s() has parameter $%s that is passed by reference.',
                $functionName,
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterWithNullDefaultValue()->toString())
                ->build();
        }, $parametersPassedByReference);
    }
}
