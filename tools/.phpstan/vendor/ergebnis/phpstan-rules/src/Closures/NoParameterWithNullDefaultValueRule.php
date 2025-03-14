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

use Ergebnis\PHPStan\Rules\Analyzer;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Expr\Closure>
 */
final class NoParameterWithNullDefaultValueRule implements Rules\Rule
{
    private Analyzer $analyzer;

    public function __construct(Analyzer $analyzer)
    {
        $this->analyzer = $analyzer;
    }

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

        $parametersWithNullDefaultValue = \array_values(\array_filter($node->params, function (Node\Param $parameter): bool {
            return $this->analyzer->hasNullDefaultValue($parameter);
        }));

        if (0 === \count($parametersWithNullDefaultValue)) {
            return [];
        }

        return \array_map(static function (Node\Param $parameterWithNullDefaultValue): Rules\RuleError {
            /** @var Node\Expr\Variable $variable */
            $variable = $parameterWithNullDefaultValue->var;

            /** @var string $parameterName */
            $parameterName = $variable->name;

            $message = \sprintf(
                'Closure has parameter $%s with null as default value.',
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterWithNullDefaultValue()->toString())
                ->build();
        }, $parametersWithNullDefaultValue);
    }
}
