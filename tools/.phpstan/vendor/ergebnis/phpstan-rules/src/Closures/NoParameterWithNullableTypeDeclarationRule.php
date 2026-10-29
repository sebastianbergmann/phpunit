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

namespace Ergebnis\PHPStan\Rules\Closures;

use Ergebnis\PHPStan\Rules\Analyzer;
use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Expr\Closure>
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
            Documentation\Description::fromString('This rule reports an error when a closure has a parameter with a nullable type declaration.'),
            Documentation\Rationale::fromString('A parameter with a nullable type declaration makes the closure handle two cases: a value and `null`. Separate closures or a null object make each case explicit.'),
            Documentation\Tip::fromString('Use separate closures or a null object instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $toUpperCase = static function (?string $value): string {
                        return \strtoupper((string) $value);
                    };
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $toUpperCase = static function (string $value): string {
                        return \strtoupper($value);
                    };
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
        return Node\Expr\Closure::class;
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
                'Closure has parameter $%s with a nullable type declaration.',
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterWithNullableTypeDeclaration()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build();
        }, $parametersWithNullableTypeDeclaration);
    }
}
