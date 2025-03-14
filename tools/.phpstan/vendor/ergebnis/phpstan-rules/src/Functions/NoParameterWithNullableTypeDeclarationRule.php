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

use Ergebnis\PHPStan\Rules\Analyzer;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Function_>
 */
final class NoParameterWithNullableTypeDeclarationRule implements Rules\Rule
{
    private Analyzer $analyzer;

    public function __construct(Analyzer $analyzer)
    {
        $this->analyzer = $analyzer;
    }

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

        $parametersWithNullableTypeDeclaration = \array_values(\array_filter($node->params, function (Node\Param $parameter): bool {
            return $this->analyzer->isNullableTypeDeclaration($parameter->type);
        }));

        if (0 === \count($parametersWithNullableTypeDeclaration)) {
            return [];
        }

        $functionName = $node->namespacedName;

        return \array_map(static function (Node\Param $parameterWithNullableTypeDeclaration) use ($functionName): Rules\RuleError {
            /** @var Node\Expr\Variable $variable */
            $variable = $parameterWithNullableTypeDeclaration->var;

            /** @var string $parameterName */
            $parameterName = $variable->name;

            $message = \sprintf(
                'Function %s() has parameter $%s with a nullable type declaration.',
                $functionName,
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterWithNullableTypeDeclaration()->toString())
                ->build();
        }, $parametersWithNullableTypeDeclaration);
    }
}
