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

namespace Ergebnis\PHPStan\Rules\Expressions;

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Expr\FuncCall>
 */
final class NoCompactRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Documentation\RuleDefinition $ruleDefinition;

    public function __construct()
    {
        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noCompact'),
            Documentation\Description::fromString('This rule reports an error when the function [`compact()`](https://www.php.net/compact) is used.'),
            Documentation\Rationale::fromString('The function `compact()` refers to variables by their names in strings, which static analysis and automated refactoring cannot follow. Build the array explicitly instead.'),
            Documentation\Tip::fromString('Build the array explicitly instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $name = 'Jane';

                    $context = \compact('name');
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $name = 'Jane';

                    $context = [
                        'name' => $name,
                    ];
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
        return Node\Expr\FuncCall::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        if (!$node->name instanceof Node\Name) {
            return [];
        }

        if ('compact' !== \mb_strtolower($scope->resolveName($node->name))) {
            return [];
        }

        return [
            Rules\RuleErrorBuilder::message('Function compact() should not be used.')
                ->identifier(ErrorIdentifier::noCompact()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
