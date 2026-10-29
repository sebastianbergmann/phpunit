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

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Function_>
 */
final class NoParameterPassedByReferenceRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'noParameterPassedByReference',
            'This rule reports an error when a function has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).',
            'A parameter passed by reference lets a function change a variable of its caller as a side effect, which makes the code harder to follow. Return the new value instead.',
            'Return the new value instead.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                function increment(int &$value): void
                {
                    ++$value;
                }
                PHP),
            Documentation\CodeSample::notReported(<<<'PHP'
                <?php

                declare(strict_types=1);

                function increment(int $value): int
                {
                    return $value + 1;
                }
                PHP),
        );
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

        $parametersPassedByReference = \array_values(\array_filter($node->params, static function (Node\Param $parameter): bool {
            return $parameter->byRef;
        }));

        if (0 === \count($parametersPassedByReference)) {
            return [];
        }

        $functionName = $node->namespacedName;

        return \array_map(function (Node\Param $parameterPassedByReference) use ($functionName): Rules\RuleError {
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
                ->identifier(ErrorIdentifier::noParameterPassedByReference()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build();
        }, $parametersPassedByReference);
    }
}
