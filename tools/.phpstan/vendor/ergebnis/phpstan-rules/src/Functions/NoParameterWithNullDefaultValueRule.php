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

namespace Ergebnis\PHPStan\Rules\Functions;

use Ergebnis\PHPStan\Rules\Analyzer;
use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Function_>
 */
final class NoParameterWithNullDefaultValueRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Analyzer $analyzer;
    private Documentation\RuleDefinition $ruleDefinition;

    public function __construct(Analyzer $analyzer)
    {
        $this->analyzer = $analyzer;

        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noParameterWithNullDefaultValue'),
            Documentation\Description::fromString('This rule reports an error when a function has a parameter with `null` as default value.'),
            Documentation\Rationale::fromString('A parameter with `null` as its default value makes the argument optional and nullable at once, so the function has to handle a missing value. Separate functions or a null object make each case explicit.'),
            Documentation\Tip::fromString('Use separate functions or a null object instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    function toUpperCase($value = null): string
                    {
                        return \strtoupper((string) $value);
                    }
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    function toUpperCase(string $value): string
                    {
                        return \strtoupper($value);
                    }
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
        return Node\Stmt\Function_::class;
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

        $functionName = $node->namespacedName;

        return \array_map(function (Node\Param $parameterWithNullDefaultValue) use ($functionName): Rules\RuleError {
            /** @var Node\Expr\Variable $variable */
            $variable = $parameterWithNullDefaultValue->var;

            /** @var string $parameterName */
            $parameterName = $variable->name;

            $message = \sprintf(
                'Function %s() has parameter $%s with null as default value.',
                $functionName,
                $parameterName,
            );

            return Rules\RuleErrorBuilder::message($message)
                ->identifier(ErrorIdentifier::noParameterWithNullDefaultValue()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build();
        }, $parametersWithNullDefaultValue);
    }
}
