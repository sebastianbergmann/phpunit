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
 * @implements Rules\Rule<Node\Expr\AssignRef>
 */
final class NoAssignByReferenceRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    public function ruleDefinition(): Documentation\RuleDefinition
    {
        return Documentation\RuleDefinition::create(
            'noAssignByReference',
            'This rule reports an error when [a variable is assigned by reference](https://www.php.net/manual/en/language.references.whatdo.php#language.references.whatdo.assign).',
            'Assigning by reference makes two variables share one value, so changing one changes the other, which makes the code harder to follow.',
            'Assign the value instead.',
        )->withCodeSamples(
            Documentation\CodeSample::reported(<<<'PHP'
                <?php

                declare(strict_types=1);

                $values = [
                    'foo',
                ];

                $alias = &$values;
                PHP),
            Documentation\CodeSample::notReported(<<<'PHP'
                <?php

                declare(strict_types=1);

                $values = [
                    'foo',
                ];

                $copy = $values;
                PHP),
        );
    }

    public function getNodeType(): string
    {
        return Node\Expr\AssignRef::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        return [
            Rules\RuleErrorBuilder::message('Assign by reference should not be used.')
                ->identifier(ErrorIdentifier::noAssignByReference()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
