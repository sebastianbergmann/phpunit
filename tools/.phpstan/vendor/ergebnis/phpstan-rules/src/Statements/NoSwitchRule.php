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

namespace Ergebnis\PHPStan\Rules\Statements;

use Ergebnis\PHPStan\Rules\Documentation;
use Ergebnis\PHPStan\Rules\ErrorIdentifier;
use PhpParser\Node;
use PHPStan\Analyser;
use PHPStan\Rules;

/**
 * @implements Rules\Rule<Node\Stmt\Switch_>
 */
final class NoSwitchRule implements
    Documentation\HasRuleDefinition,
    Rules\Rule
{
    private Documentation\RuleDefinition $ruleDefinition;

    public function __construct()
    {
        $this->ruleDefinition = Documentation\RuleDefinition::create(
            Documentation\ConfigurationKey::fromString('noSwitch'),
            Documentation\Description::fromString('This rule reports an error when the statement [`switch()`](https://www.php.net/manual/control-structures.switch.php) is used.'),
            Documentation\Rationale::fromString('A `switch` statement compares loosely and falls through to the next case without `break`, which can cause subtle bugs. Use `if` statements or `match` instead.'),
            Documentation\Tip::fromString('Use `if` statements or `match` instead.'),
            Documentation\ParameterList::create(),
            Documentation\CodeSampleList::create(
                Documentation\CodeSample::reported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $status = 'active';

                    switch ($status) {
                        case 'active':
                            echo 'Active';

                            break;

                        default:
                            echo 'Inactive';
                    }
                    PHP),
                Documentation\CodeSample::notReported(<<<'PHP'
                    <?php

                    declare(strict_types=1);

                    $status = 'active';

                    if ('active' === $status) {
                        echo 'Active';
                    } else {
                        echo 'Inactive';
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
        return Node\Stmt\Switch_::class;
    }

    public function processNode(
        Node $node,
        Analyser\Scope $scope
    ): array {
        return [
            Rules\RuleErrorBuilder::message('Control structures using switch should not be used.')
                ->identifier(ErrorIdentifier::noSwitch()->toString())
                ->tip(Documentation\ErrorTip::for($this))
                ->build(),
        ];
    }
}
