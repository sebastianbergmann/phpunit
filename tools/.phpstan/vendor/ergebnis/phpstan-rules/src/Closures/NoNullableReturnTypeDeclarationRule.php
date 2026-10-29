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
final class NoNullableReturnTypeDeclarationRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Analyzer $analyzer;
    private Documentation\RuleDefinition $ruleDefinition;

    public function __construct(Analyzer $analyzer)
    {
        $this->analyzer = $analyzer;

        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noNullableReturnTypeDeclaration'),
            Documentation\Description::fromString('This rule reports an error when a closure uses a nullable return type declaration.'),
            Documentation\Rationale::fromString('A nullable return type forces every caller to handle `null`. Throwing an exception, returning a null object, or splitting the closure in two keeps the callers simpler.'),
            Documentation\Tip::fromString('Throw an exception, return a null object, or split the closure in two instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $toUpperCase = static function (string $value): ?string {
                        return \strtoupper($value);
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
        if (!$this->analyzer->isNullableTypeDeclaration($node->getReturnType())) {
            return [];
        }

        return [
            Rules\RuleErrorBuilder::message('Closure has a nullable return type declaration.')
                ->identifier(ErrorIdentifier::noNullableReturnTypeDeclaration()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
