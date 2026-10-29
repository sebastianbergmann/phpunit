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

namespace Ergebnis\PHPStan\Rules\ArrowFunctions;

use Ergebnis\PHPStan\Rules\Analyzer;
use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Expr\ArrowFunction>
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
            Documentation\Description::fromString('This rule reports an error when an arrow function has a parameter with a nullable type declaration.'),
            Documentation\Rationale::fromString('A parameter with a nullable type declaration makes the arrow function handle two cases: a value and `null`. Separate arrow functions or a null object make each case explicit.'),
            Documentation\Tip::fromString('Use separate arrow functions or a null object instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $toUpperCase = static fn (?string $value): string => \strtoupper((string) $value);
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $toUpperCase = static fn (string $value): string => \strtoupper($value);
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
        return Node\Expr\ArrowFunction::class;
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

        return \array_map(function (Node\Param $parameterWithNullableTypeDeclaration): Rules\RuleError {
            /** @var Node\Expr\Variable $variable */
            $variable = $parameterWithNullableTypeDeclaration->var;

            /** @var string $parameterName */
            $parameterName = $variable->name;

            $message = \sprintf(
                'Arrow function has parameter $%s with a nullable type declaration.',
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterWithNullableTypeDeclaration()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build();
        }, $parametersWithNullableTypeDeclaration);
    }
}
