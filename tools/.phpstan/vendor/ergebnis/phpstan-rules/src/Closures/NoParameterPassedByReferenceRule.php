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

namespace Ergebnis\PHPStan\Rules\Closures;

use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Expr\Closure>
 */
final class NoParameterPassedByReferenceRule implements Rules\Rule
{
    public function getNodeType(): string
    {
        return Node\Expr\Closure::class;
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

        return \array_map(static function (Node\Param $parameterPassedByReference): Rules\RuleError {
            /** @var Node\Expr\Variable $variable */
            $variable = $parameterPassedByReference->var;

            /** @var string $parameterName */
            $parameterName = $variable->name;

            $message = \sprintf(
                'Closure has parameter $%s that is passed by reference.',
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterPassedByReference()->toString())
                ->build();
        }, $parametersPassedByReference);
    }
}
